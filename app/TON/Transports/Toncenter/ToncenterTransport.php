<?php declare(strict_types=1);

namespace App\TON\Transports\Toncenter;

use App\TON\Transports\Toncenter\Models\TonResponse;
use Brick\Math\BigNumber;
use App\TON\Interop\Address;
use App\TON\Interop\Boc\Cell;
use App\TON\Interop\Boc\Exceptions\CellException;
use App\TON\AddressState;
use App\TON\Contract;
use App\TON\Contracts\Exceptions\ContractException;
use App\TON\Contracts\Messages\Exceptions\MessageException;
use App\TON\Contracts\Messages\Exceptions\ResponseStackParsingException;
use App\TON\Contracts\Messages\ExternalMessage;
use App\TON\Contracts\Messages\ResponseStack;
use App\TON\Exceptions\TransportException;
use App\TON\Transport;
use App\TON\Transports\Toncenter\Exceptions as TncEx;
use App\TON\TypedArrays\Uint8Array;

class ToncenterTransport implements Transport
{
    private ToncenterV2Client $client;

    public function __construct(
        ToncenterV2Client $client
    ) {
        $this->client = $client;
    }

    /**
     * @inheritDoc
     */
    public function runGetMethod($contract, string $method, array $stack = []): ResponseStack
    {
        try {
            $address = $contract instanceof Contract ? $contract->getAddress() : $contract;
        } catch (ContractException $e) {
            throw new TransportException(
                "Contract address error: " . $e->getMessage(),
                0,
                $e,
            );
        }

        try {
            $sStack = ToncenterStackSerializer::serialize($stack);
        } catch (\Throwable $e) {
            throw new TransportException(
                "Stack serialization error: " . $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }

        try {
            $response = $this
                ->client
                ->runGetMethod(
                    $address,
                    $method,
                    $sStack,
                );

            if (!in_array($response->exitCode, [0, 1], true)) {
                throw new TransportException(
                    "Non-zero exit code, code: " . $response->exitCode,
                    $response->exitCode,
                );
            }
            return ToncenterResponseStack::parse($response->stack);
        } catch (TncEx\ClientException | TncEx\TimeoutException | TncEx\ValidationException $e) {
            throw new TransportException(
                sprintf(
                    "Get method error: %s; address: %s, method: %s",
                    $e->getMessage(),
                    $address->toString(true),
                    $method,
                ),
                0,
                $e,
            );
        } catch (ResponseStackParsingException $e) {
            throw new TransportException(
                sprintf(
                    "Stack parsing error: %s; address: %s, method: %s",
                    $e->getMessage(),
                    $address->toString(true),
                    $method,
                ),
                0,
                $e,
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function send($boc): void
    {
        try {
            $this
                ->client
                ->sendBoc(
                    $boc,
                );
        } catch (TncEx\ClientException | TncEx\TimeoutException | TncEx\ValidationException $e) {
            throw new TransportException(
                sprintf(
                    "Sending error: %s",
                    $e->getMessage(),
                ),
                0,
                $e,
            );
        }
    }

    /**
     * @throws TransportException
     */
    public function sendReturnHash($boc): TonResponse
    {
        try {
            return $this
                ->client
                ->sendBocReturnHash(
                    $boc,
                );
        } catch (TncEx\ClientException | TncEx\TimeoutException | TncEx\ValidationException $e) {
            throw new TransportException(
                sprintf(
                    "Sending error: %s",
                    $e->getMessage(),
                ),
                0,
                $e,
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function sendMessage(ExternalMessage $message, Uint8Array $secretKey): void
    {
        try {
            $this->send($message->sign($secretKey)->toBoc(false));
        } catch (CellException | MessageException $e) {
            throw new TransportException(
                sprintf(
                    "Message sending error: %s",
                    $e->getMessage(),
                ),
                0,
                $e,
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function sendMessageReturnHash(ExternalMessage $message, Uint8Array $secretKey): TonResponse
    {
        try {
            return $this->sendReturnHash($message->sign($secretKey)->toBoc(false));
        } catch (CellException | MessageException $e) {
            throw new TransportException(
                sprintf(
                    "Message sending error: %s",
                    $e->getMessage(),
                ),
                0,
                $e,
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function estimateFee(Address $address,
                                $body,
                                $initCode = null,
                                $initData = null): BigNumber
    {
        try {
            return $this
                ->client
                ->estimateFee(
                    $address->toString(true, true, false),
                    $body,
                    $initCode,
                    $initData,
                )
                ->sourceFees
                ->sum();
        } catch (TncEx\ClientException | TncEx\TimeoutException | TncEx\ValidationException$e) {
            throw new TransportException(
                $e->getMessage(),
                0,
                $e,
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function getConfigParam(int $configParamId): Cell
    {
        try {
            $answer = $this->client->getConfigParam($configParamId);

            if ($answer->type !== "tvm.cell") {
                throw new TransportException();
            }

            return Cell::oneFromBoc($answer->bytes, true);
        } catch (TncEx\ClientException | TncEx\TimeoutException | TncEx\ValidationException | CellException $e) {
            throw new TransportException(
                $e->getMessage(),
                0,
                $e,
            );
        }
    }

    /**
     * @throws TransportException
     */
    public function getState(Address $address): AddressState
    {
        try {
            return $this->client->getAddressState($address);
        } catch (TncEx\ClientException | TncEx\TimeoutException | TncEx\ValidationException $e) {
            throw new TransportException(
                $e->getMessage(),
                0,
                $e,
            );
        }
    }
}
