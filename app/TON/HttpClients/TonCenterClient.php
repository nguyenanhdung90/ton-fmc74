<?php

namespace App\TON\HttpClients;

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

    public function jsonRPC(array $params): array
    {
        try {
            $uri = $this->baseUri . 'api/v2/jsonRPC';
            Arr::set($options, 'body', json_encode($params));
            $response = $this->client->request('POST', $uri, $options);
            $content = $response->getBody()->getContents();
            return json_decode($content, true);
        } catch (GuzzleException $e) {
            Log::error('Caught exception: ' . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function getJettonWallets(array $params) {
        try {
            $uri = $this->baseUri . 'api/v3/jetton/wallets?' . http_build_query($params);
            $response = $this->client->request('GET', $uri);
            $content = $response->getBody()->getContents();
            return json_decode($content, true);
        } catch (GuzzleException $e) {
            Log::error('Caught exception get Jetton Wallets: ' . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function getJettonMasters(array $params)
    {
        try {
            $uri = $this->baseUri . 'api/v3/jetton/masters?' . http_build_query($params);
            Log::info($uri);
            $response = $this->client->request('GET', $uri);
            $content = $response->getBody()->getContents();
            return json_decode($content, true);
        } catch (GuzzleException $e) {
            Log::error('Caught exception getJettonMasters: ' . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
