<?php

namespace App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet;

use App\Models\WalletTonTransaction;

abstract class TransactionUpdateToWalletAbstract
{
    protected int $transactionId;

    public function __construct(int $transactionId)
    {
        $this->transactionId = $transactionId;
    }

    abstract public function getUpdateTransactionToWallet(): UpdateAmountFeeTransactionInterface;

    public function updateToAmountWallet()
    {
        $updateTransactionToWallet = $this->getUpdateTransactionToWallet();
        $updateTransactionToWallet->process();
    }
}
