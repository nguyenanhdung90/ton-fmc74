<?php

namespace App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet;

class TransactionWithdrawAmount extends TransactionUpdateToWalletAbstract
{
    public function getUpdateTransactionToWallet(): UpdateAmountFeeTransactionInterface
    {
        return new UpdateWithdrawAmountTransaction($this->transaction);
    }
}
