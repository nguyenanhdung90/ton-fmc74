<?php

namespace App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet;

class TransactionRevokeWithdrawAmount extends TransactionUpdateToWalletAbstract
{
    public function getUpdateTransactionToWallet(): SyncTransactionInterface
    {
        return new RevokeWithdrawAmountTransaction($this->transactionId);
    }
}
