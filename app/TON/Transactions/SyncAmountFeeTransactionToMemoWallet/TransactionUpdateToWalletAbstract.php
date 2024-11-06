<?php

namespace App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet;

abstract class TransactionUpdateToWalletAbstract
{
    protected int $transactionId;

    public function __construct(int $transactionId)
    {
        $this->transactionId = $transactionId;
    }

    abstract public function getUpdateTransactionToWallet(): SyncTransactionInterface;

    public function syncTransactionWallet(array $data = [])
    {
        $updateTransactionToWallet = $this->getUpdateTransactionToWallet();
        $updateTransactionToWallet->process($data);
    }
}
