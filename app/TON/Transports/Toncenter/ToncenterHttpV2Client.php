<?php declare(strict_types=1);

namespace App\TON\Transports\Toncenter;

use App\TON\Interop\Boc\Cell;
use App\TON\Interop\Boc\Exceptions\CellException;
use App\TON\Interop\Bytes;
use App\TON\Transports\Toncenter\Exceptions\ClientException;
use App\TON\Transports\Toncenter\Exceptions\TimeoutException;
use App\TON\Transports\Toncenter\Exceptions\ValidationException;
use App\TON\Transports\Toncenter\Models\JsonRpcResponse;
use App\TON\Transports\Toncenter\Models\TonResponse;
use App\TON\TypedArrays\Uint8Array;
use Http\Client\Common\HttpMethodsClientInterface;
use Psr\Http\Client\ClientExceptionInterface;

class ToncenterHttpV2Client implements ToncenterV2Client
{
    private HttpMethodsClientInterface $httpClient;
    private ClientOptions $options;
    public function __construct(HttpMethodsClientInterface $httpClient,
                                ClientOptions $options
    ) {
        $this->httpClient = $httpClient;
        $this->options = $options;
    }


    /**
     * @inheritDoc
     */
    public function sendBoc($boc): TonResponse
    {
        return $this
            ->query([
                "method" => "sendBoc",
                "params" => [
                    "boc" => $this->serializeBoc($boc),
                ],
            ])
            ->asTonResponse();
    }

    /**
     * @inheritDoc
     */
    public function sendBocReturnHash($boc): TonResponse
    {
        return $this
            ->query([
                "method" => "sendBocReturnHash",
                "params" => [
                    "boc" => $this->serializeBoc($boc),
                ],
            ])
            ->asTonResponse();
    }

    /**
     * @inheritDoc
     */
    public function sendQuery(array $body): TonResponse
    {
        return $this
            ->query([
                "method" => "sendQuery",
                "params" => $body,
            ])
            ->asTonResponse();
    }



    /**
     * @inheritDoc
     */
    public function jsonRPC(array $params): JsonRpcResponse
    {
        return $this->query($params);
    }

    /**
     * @throws ClientException
     */
    private function serializeBoc($boc): string
    {
        if (is_string($boc) && !preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $boc)) {
            throw new \InvalidArgumentException(
                "If a BoC string is passed, then it must be a base64-serialized string",
            );
        }

        if ($boc instanceof Cell) {
            try {
                $boc = $boc->toBoc(false);
            } catch (CellException $e) {
                throw new ClientException(
                    "Boc serialization error: " . $e->getMessage(),
                    $e->getCode(),
                    $e,
                );
            }
        }

        if ($boc instanceof Uint8Array) {
            $boc = Bytes::bytesToBase64($boc);
        }

        if (!is_string($boc)) {
            throw new \RuntimeException("Unexpected BoC serialization error");
        }

        return $boc;
    }

    /**
     * @param array{method: string, params: array, jsonrpc?: string, id?: string} $params
     * @throws ClientException
     * @throws ValidationException
     * @throws TimeoutException
     */
    private function query(array $params): JsonRpcResponse
    {
        $headers = [
            "Content-Type" => "application/json",
            "Accept" => "application/json",
            "User-Agent" => "php-olifanton-client/php-" . PHP_VERSION,
        ];

        if ($this->options->apiKey) {
            $headers["X-Api-Key"] = $this->options->apiKey;
        }

        if (!isset($params["jsonrpc"])) {
            $params["jsonrpc"] = "2.0";
        }

        if (!isset($params["id"])) {
            $params["id"] = (string)hrtime(true);
        }

        try {
            $response = $this
                ->httpClient
                ->send(
                    "POST",
                    $this->options->baseUri . "/jsonRPC",
                    $headers,
                    json_encode($params, JSON_THROW_ON_ERROR),
                );

            $statusCode = $response->getStatusCode();

            if ($statusCode === 200) {
                if ($this->options->requestDelay > 0) {
                    usleep((int)($this->options->requestDelay * 1000000));
                }

                return $this->hydrateJsonRpcResponse($response->getBody()->getContents());
            }

            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody()->getContents();
            [$errCode, $errMessage] = $this->tryExtractError($responseBody);

            if ($statusCode === 422) {
                throw new ValidationException(
                    ($errMessage) ?: "Validation error",
                    ($errCode) ?: $statusCode,
                );
            } else if ($statusCode === 504) {
                throw new TimeoutException(
                    ($errMessage) ?: "Lite Server Timeout",
                    ($errCode) ?: $statusCode,
                );
            }

            throw new ClientException(
                ($errMessage) ?: "Toncenter request error: " . $response->getReasonPhrase(),
                ($errCode) ?: $statusCode,
            );
        } catch (\JsonException $e) {
            throw new ClientException(
                "JSON RPC body serialization error: " . $e->getMessage(),
                $e->getCode(),
                $e
            );
        } catch (ClientExceptionInterface $e) {
            throw new ClientException(
                "Toncenter client request error: " . $e->getMessage(),
                0,
                $e,
            );
        }
    }

    /**
     * @throws ClientException
     */
    private function hydrateJsonRpcResponse(string $responseBody): JsonRpcResponse
    {
        try {
            $body = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);

            if (isset($body["ok"], $body["result"], $body["jsonrpc"])) {
                return new JsonRpcResponse(
                    (bool)$body["ok"],
                    $body["result"],
                    isset($body["error"]) ? (string)$body["error"] : null,
                    isset($body["code"])? (int)$body["code"] : null,
                    (string)$body["jsonrpc"],
                    isset($body["id"]) ? (string)$body["id"] : null,
                );
            }

            throw new ClientException("Invalid JSON RPC answer: `" . $responseBody . "`");
        } catch (\JsonException $e) {
            throw new ClientException(
                "JSON RPC answer body parsing error: " . $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
    }


    private function tryExtractError(string $responseBody): array
    {
        try {
            $body = json_decode($responseBody, true, 32, JSON_THROW_ON_ERROR);

            if (isset($body["error"], $body["code"])) {
                return [(int)$body["code"], $body["error"]];
            }
        } catch (\Throwable $t) {}

        return [null, null];
    }
}
