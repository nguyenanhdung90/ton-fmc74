<?php

namespace App\TON\Transactions\SyncTransactionToWallet;

abstract class TransactionToWalletAbstract
{
    protected int $transactionId;

    public function __construct(int $transactionId)
    {
        $this->transactionId = $transactionId;
    }

    abstract public function getUpdateTransactionToWallet(): SyncTransactionInterface;

    public function syncTransactionWallet(?array $data = [])
    {
        $updateTransactionToWallet = $this->getUpdateTransactionToWallet();
        $updateTransactionToWallet->process($data);
    }
}
