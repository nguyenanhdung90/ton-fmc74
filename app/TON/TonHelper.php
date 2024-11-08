<?php

namespace App\TON;

use App\TON\Interop\Units;

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
                return [
                    'decimals' => Units::USDt,
                    'symbol' => self::USDT
                ];
            case strtoupper(config('services.ton.master_jetton_not')):
                return [
                    'decimals' => Units::NOT,
                    'symbol' => self::NOT
                ];
            default:
                return self::NONSUPPORT_JETTON;
        }
    }

    public static function generateRandomString($length = 10)
    {
        return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            ceil($length / strlen($x)))), 1, $length);
    }
}
