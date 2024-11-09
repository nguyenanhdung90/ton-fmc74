<?php

namespace App\TON\Transactions\SyncTransactionToWallet;

class TransactionWithdrawSuccess extends TransactionToWalletAbstract
{
    public function getUpdateTransactionToWallet(): SyncTransactionInterface
    {
        return new UpdateWithdrawSuccessTransaction($this->transactionId);
    }
}
