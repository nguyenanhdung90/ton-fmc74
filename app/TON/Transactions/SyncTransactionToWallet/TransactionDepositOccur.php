<?php

namespace App\TON\Transactions\SyncTransactionToWallet;

class TransactionDepositOccur extends TransactionToWalletAbstract
{
    public function getUpdateTransactionToWallet(): SyncTransactionInterface
    {
        return new UpdateDepositOccurTransaction($this->transactionId);
    }
}
