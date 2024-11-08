<?php

namespace App\TON\HttpClients;

use App\TON\Exceptions\InvalidJsonRpcException;
use App\TON\TonHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class TonCenterClient implements TonCenterClientInterface
{
    /**
     * @var string
     */
    private string $baseUri;

    /**
     * @var Client
     */
    private Client $client;

    /**
     * @var array
     */
    private array $options;

    public function __construct()
    {
        $this->baseUri = config('services.ton.is_main') ? self::MAIN_BASE_URI : self::TEST_BASE_URI;
        $this->client = new Client();
        $this->options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'accept' => 'application/json',
                'X-API-Key' => config('services.ton.api_key'),
            ]
        ];
    }

    public function jsonRPC(array $query): array
    {
        try {
            $idQuery = TonHelper::generateRandomString(6);
            $rpcQuery = array_merge($query, ['id' => $idQuery, "jsonrpc" => "2.0"]);
            Arr::set($options, 'body', json_encode($rpcQuery));
            $response = $this->client->request('POST', $this->baseUri . 'api/v2/jsonRPC', $options);
            $contents = json_decode($response->getBody()->getContents(), true);
            if (Arr::get($contents, 'id') !== $idQuery) {
                throw new InvalidJsonRpcException('Invalid id query json rpc');
            }
            return $contents;
        } catch (GuzzleException | InvalidJsonRpcException $e) {
            Log::error('Caught exception: ' . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function getTransactionJsonRPC(array $params): Collection
    {
        $query = [
            "method" => "getTransactions",
            "params" => array_merge(array_filter($params), ["archival" => true])
        ];
        $data = $this->jsonRPC($query);
        if (!$data['ok']) {
            printf("Error from TonCenter: %s \n", json_encode($query));
        }
        return $data['ok'] ? collect($data['result']) : collect([]);
    }

    public function getJetWallets(array $params): ?Collection
    {
        try {
            $uri = $this->baseUri . 'api/v3/jetton/wallets?' . http_build_query(array_filter($params));
            $response = $this->client->request('GET', $uri);
            if ($response->getStatusCode() !== 200) {
                return null;
            }
            $content = $response->getBody()->getContents();
            $result = json_decode($content, true);
            $jetWallets = Arr::get($result, 'jetton_wallets', []);
            return collect($jetWallets);
        } catch (GuzzleException $e) {
            Log::error('Caught exception get Jetton Wallets: ' . $e->getMessage());
            printf("Caught exception get Jetton Wallets: %s \n", $e->getMessage());
            return null;
        }
    }

    public function getJetMasters(array $params): ?Collection
    {
        try {
            $uri = $this->baseUri . 'api/v3/jetton/masters?' . http_build_query(array_filter($params));
            $response = $this->client->request('GET', $uri);
            if ($response->getStatusCode() !== 200) {
                return null;
            }
            $content = $response->getBody()->getContents();
            $result = json_decode($content, true);
            $jetMasters = Arr::get($result, 'jetton_masters', []);
            return collect($jetMasters);
        } catch (GuzzleException $e) {
            Log::error('Caught exception getJettonMasters: ' . $e->getMessage());
            printf("Caught exception getJettonMasters: %s \n", $e->getMessage());
            return null;
        }
    }

    public function getTransactionsByMessage(array $params): ?Collection
    {
        try {
            $uri = $this->baseUri . 'api/v3/transactionsByMessage?' . http_build_query(array_filter($params));
            $response = $this->client->request('GET', $uri);
            if ($response->getStatusCode() !== 200) {
                return null;
            }
            $content = $response->getBody()->getContents();
            $result = json_decode($content, true);
            $transactions = Arr::get($result, 'transactions', []);
            return collect($transactions);
        } catch (GuzzleException $e) {
            Log::error('Caught exception getTransactionsByMessage: ' . $e->getMessage());
            printf("Caught exception getTransactionsByMessage: %s \n", $e->getMessage());
            return null;
        }
    }

    public function getTransactionsBy(array $params): ?Collection
    {
        try {
            $uri = $this->baseUri . 'api/v3/transactions?' . http_build_query(array_filter($params));
            $response = $this->client->request('GET', $uri);
            if ($response->getStatusCode() !== 200) {
                return null;
            }
            $content = $response->getBody()->getContents();
            $result = json_decode($content, true);
            $transactions = Arr::get($result, 'transactions', []);
            return collect($transactions);
        } catch (GuzzleException $e) {
            Log::error('Caught exception getTransactionsBy: ' . $e->getMessage());
            printf("Caught exception getTransactionsBy: %s \n", $e->getMessage());
            return null;
        }
    }
}
