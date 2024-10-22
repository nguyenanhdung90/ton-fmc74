<?php declare(strict_types=1);

namespace App\TON\Transports\Toncenter;

class ClientOptions
{
    public const MAIN_BASE_URL = "https://toncenter.com/api/v2";
    public const TEST_BASE_URL = "https://testnet.toncenter.com/api/v2";

    public string $baseUri;
    public ?string $apiKey;
    public ?float $requestDelay;

    public function __construct(
        string $baseUri = self::MAIN_BASE_URL,
        ?string $apiKey = null,
        ?float $requestDelay = 0.0
    ) {
        $this->baseUri = $baseUri;
        $this->apiKey = $apiKey;
        $this->requestDelay = $requestDelay;
    }
}
