<?php

namespace App\TON\Transactions;

class TransactionHelper
{
    const TON_DECIMALS = 9;

    const BATCH_NUMBER_JETTON_WALLET = 20;

    const BATCH_NUMBER_JETTON_MASTER = 15;

    const MAX_LIMIT_TRANSACTION = 100;

    /**
     * @throws \Exception
     */
    public static function uniqueTransactionHash(): string
    {
        $bytes = random_bytes(22);
        return bin2hex($bytes);
    }
}
