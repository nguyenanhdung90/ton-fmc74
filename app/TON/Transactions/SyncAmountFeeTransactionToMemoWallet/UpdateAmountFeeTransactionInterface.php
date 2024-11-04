<?php

namespace App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet;

interface UpdateAmountFeeTransactionInterface
{
    public function process(array $data);
}
