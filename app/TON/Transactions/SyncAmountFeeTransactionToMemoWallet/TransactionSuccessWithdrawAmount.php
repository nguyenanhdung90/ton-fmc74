<?php

namespace App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet;

class TransactionSuccessWithdrawAmount extends TransactionUpdateToWalletAbstract
{
    public function getUpdateTransactionToWallet(): SyncTransactionInterface
    {
        return new UpdateSuccessWithdrawAmountTransaction($this->transactionId);
    }
}
