<?php

namespace App\TON\Transactions;

class TransactionHelper
{
    const BATCH_NUMBER_JETTON_WALLET = 20;
    const BATCH_NUMBER_JETTON_MASTER = 15;
    const MAX_LIMIT_TRANSACTION = 100;
    const TON = 'TON';
    const USDT = 'USDT';
    const DEPOSIT = 'DEPOSIT';
    const WITHDRAW = 'WITHDRAW';
    const WITHDRAW_EXCESS = 'WITHDRAW_EXCESS';
    const JET_OPCODE = '7362d09c';
    const EXCESS_OPCODE = 'd53276db';

    /**
     * @throws \Exception
     */
    public static function uniqueTransactionHash(): string
    {
        $bytes = random_bytes(22);
        return bin2hex($bytes);
    }
}
