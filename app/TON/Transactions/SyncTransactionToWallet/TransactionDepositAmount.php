<?php

namespace App\TON\Transactions\SyncTransactionToWallet;

class TransactionDepositAmount extends TransactionToWalletAbstract
{
    public function getUpdateTransactionToWallet(): SyncTransactionInterface
    {
        return new UpdateDepositAmountTransaction($this->transactionId);
    }
}
