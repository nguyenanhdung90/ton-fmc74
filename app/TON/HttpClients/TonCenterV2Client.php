<?php

namespace App\TON\HttpClients;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class TonCenterV2Client implements TonCenterV2ClientInterface
{
    /**
     * @var string
     */
    private string $baseUri;

    /**
     * @var string
     */
    private string $apiKey;

    public function __construct()
    {
        $this->baseUri = config('services.ton.is_main') ? config('services.ton.base_uri_ton_center_main') :
            config('services.ton.base_uri_ton_center_test');
        $this->apiKey = config('services.ton.is_main') ? config('services.ton.api_key_main') :
            config('services.ton.api_key_test');
    }

    public function jsonRPC(array $params): array
    {
        try {
            $client = new Client();
            $options['headers'] = [
                'Content-Type' => 'application/json',
                'accept' => 'application/json',
                'X-API-Key' => $this->apiKey,
            ];
            $uri = $this->baseUri . 'api/v2/jsonRPC';
            $options['body'] = json_encode($params);
            $response = $client->request('POST', $uri, $options);
            $content = $response->getBody()->getContents();
            return json_decode($content, true);
        } catch (GuzzleException $e) {
            Log::error('Caught exception: ' . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
