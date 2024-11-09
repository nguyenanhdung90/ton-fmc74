<?php

namespace App\TON\Transactions\SyncTransactionToWallet;

class TransactionWithdrawRevokeAmount extends TransactionToWalletAbstract
{
    public function getUpdateTransactionToWallet(): SyncTransactionInterface
    {
        return new RevokeWithdrawAmountTransaction($this->transactionId);
    }
}
