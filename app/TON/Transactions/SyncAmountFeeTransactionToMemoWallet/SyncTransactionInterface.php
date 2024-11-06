<?php

namespace App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet;

interface SyncTransactionInterface
{
    public function process(array $data);
}
