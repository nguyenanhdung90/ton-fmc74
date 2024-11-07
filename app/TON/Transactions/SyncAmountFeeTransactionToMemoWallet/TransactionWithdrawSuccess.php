<?php

namespace App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet;

class TransactionWithdrawSuccess extends TransactionToWalletAbstract
{
    public function getUpdateTransactionToWallet(): SyncTransactionInterface
    {
        return new UpdateWithdrawSuccessTransaction($this->transactionId);
    }
}
