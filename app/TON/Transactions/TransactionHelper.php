<?php

namespace App\TON\Transactions;

class TransactionHelper
{
    const BATCH_NUMBER_JETTON_WALLET = 20;
    const BATCH_NUMBER_JETTON_MASTER = 15;
    const MAX_LIMIT_TRANSACTION = 100;
    const TON = 'TON';
    const USDT = 'USDT';
    const PAYN = 'PAYN';
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
        'decimals' => 0,
        'symbol' => self::NONSUPPORT_SYMBOL
    ];
    const NONSUPPORT_SYMBOL = 'NONSUPPORT';

    public static function validJettonAttribute(): array
    {
        return [
            config('services.ton.root_usdt') => [
                'decimals' => 6,
                'symbol' => 'USDT'
            ],
            config('services.ton.root_not') => [
                'decimals' => 9,
                'symbol' => 'NOT'
            ]
        ];
    }
}
