<?php

namespace App\TON;

use App\TON\Interop\Units;
use App\TON\Transports\Toncenter\ClientOptions;
use App\TON\Transports\Toncenter\ToncenterHttpV2Client;
use App\TON\Transports\Toncenter\ToncenterTransport;
use Http\Client\Common\HttpMethodsClient;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;

class TonHelper
{
    const BATCH_NUMBER_JETTON_WALLET = 20;
    const MAX_LIMIT_TRANSACTION = 100;
    const TON = 'TON';
    const USDT = 'USDT';
    const PAYN = 'PAYN';
    const NOT = 'NOT';
    const AIOTX = 'AIOTX';
    const DEPOSIT = 'DEPOSIT';
    const WITHDRAW = 'WITHDRAW';
    const WITHDRAW_EXCESS = 'WITHDRAW_EXCESS';
    const JET_OPCODE = '7362d09c';
    const EXCESS_OPCODE = 'd53276db';
    const INITIATED = 'INITIATED';
    const PROCESSING = 'PROCESSING';
    const SUCCESS = 'SUCCESS';
    const FAILED = 'FAILED';

    const NONSUPPORT_JETTON = [
        'decimals' => null,
        'symbol' => self::NONSUPPORT_SYMBOL
    ];
    const NONSUPPORT_SYMBOL = 'NONSUPPORT';

    public static function getJettonAttribute(string $hexAddressJettonMaster): array
    {
        switch ($hexAddressJettonMaster) {
            case strtoupper(config('services.ton.master_jetton_usdt')):
                $attribute = [
                    'decimals' => Units::USDt,
                    'symbol' => self::USDT
                ];
                break;
            case strtoupper(config('services.ton.master_jetton_not')):
                $attribute = [
                    'decimals' => Units::NOT,
                    'symbol' => self::NOT
                ];
                break;
            default:
                $attribute = self::NONSUPPORT_JETTON;
                break;
        }
        return array_merge($attribute, ['hex_address' => $hexAddressJettonMaster]);
    }

    public static function generateRandomString($length = 10)
    {
        return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            ceil($length / strlen($x)))), 1, $length);
    }

    public static function getBaseUri(): string
    {
        return config('services.ton.is_main') ? ClientOptions::MAIN_BASE_URL : ClientOptions::TEST_BASE_URL;
    }

    public static function getTransport(): ToncenterTransport
    {
        $httpClient = new HttpMethodsClient(
            Psr18ClientDiscovery::find(),
            Psr17FactoryDiscovery::findRequestFactory(),
            Psr17FactoryDiscovery::findStreamFactory(),
        );
        $tonCenter = new ToncenterHttpV2Client(
            $httpClient,
            new ClientOptions(
                self::getBaseUri(),
                config('services.ton.api_key')
            )
        );
        return new ToncenterTransport($tonCenter);
    }
}
