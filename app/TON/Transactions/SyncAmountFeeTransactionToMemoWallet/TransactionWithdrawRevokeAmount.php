<?php

namespace App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet;

class TransactionWithdrawRevokeAmount extends TransactionToWalletAbstract
{
    public function getUpdateTransactionToWallet(): SyncTransactionInterface
    {
        return new RevokeWithdrawAmountTransaction($this->transactionId);
    }
}
