<?php

namespace App\TON\Transactions\SyncTransactionToWallet;

interface SyncTransactionInterface
{
    public function process(?array $data);
}
