<?php

namespace App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet;

class TransactionRevokeWithdrawAmount extends TransactionToWalletAbstract
{
    public function getUpdateTransactionToWallet(): SyncTransactionInterface
    {
        return new RevokeWithdrawAmountTransaction($this->transactionId);
    }
}
