<?php

namespace App\TON\HttpClients;

use App\TON\TonHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class TonCenterClient implements TonCenterClientInterface
{
    /**
     * @var string
     */
    private string $baseUri;

    /**
     * @var string
     */
    private string $apiKey;

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
        $this->baseUri = config('services.ton.is_main') ? config('services.ton.base_uri_ton_center_main') :
            config('services.ton.base_uri_ton_center_test');
        $this->apiKey = config('services.ton.is_main') ? config('services.ton.api_key_main') :
            config('services.ton.api_key_test');
        $this->client = new Client();
        $this->options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'accept' => 'application/json',
                'X-API-Key' => $this->apiKey,
            ]
        ];
    }

    public function jsonRPC(array $query): array
    {
        try {
            $uri = $this->baseUri . 'api/v2/jsonRPC';
            $rpcQuery = array_merge($query, ['id' => TonHelper::random(6), "jsonrpc" => "2.0"]);
            Arr::set($options, 'body', json_encode($rpcQuery));
            $response = $this->client->request('POST', $uri, $options);
            $content = $response->getBody()->getContents();
            return json_decode($content, true);
        } catch (GuzzleException $e) {
            Log::error('Caught exception: ' . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function getJettonWallets(array $params): array
    {
        try {
            $uri = $this->baseUri . 'api/v3/jetton/wallets?' . http_build_query($params);
            $response = $this->client->request('GET', $uri);
            if ($response->getStatusCode() !== 200) {
                return ['ok' => false];
            }
            $content = $response->getBody()->getContents();
            return ['ok' => true, 'data' => json_decode($content, true)];
        } catch (GuzzleException $e) {
            Log::error('Caught exception get Jetton Wallets: ' . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function getJettonMasters(array $params): array
    {
        try {
            $uri = $this->baseUri . 'api/v3/jetton/masters?' . http_build_query($params);
            $response = $this->client->request('GET', $uri);
            if ($response->getStatusCode() !== 200) {
                return ['ok' => false];
            }
            $content = $response->getBody()->getContents();
            return ['ok' => true, 'data' => json_decode($content, true)];
        } catch (GuzzleException $e) {
            Log::error('Caught exception getJettonMasters: ' . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
