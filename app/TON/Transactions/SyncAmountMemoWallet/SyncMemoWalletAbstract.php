<?php

namespace App\TON\Transactions\SyncAmountMemoWallet;

use App\Models\WalletTonTransaction;

abstract class SyncMemoWalletAbstract
{
    protected WalletTonTransaction $transaction;

    public function __construct(WalletTonTransaction $transaction)
    {
        $this->transaction = $transaction;
    }

    abstract public function process(): void;
}
