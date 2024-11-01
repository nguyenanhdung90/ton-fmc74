<?php

namespace App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet;

class TransactionWithdrawFee extends TransactionUpdateToWalletAbstract
{
    public function getUpdateTransactionToWallet(): UpdateAmountFeeTransactionInterface
    {
        return new UpdateWithdrawFeeTransaction($this->transaction);
    }
}
