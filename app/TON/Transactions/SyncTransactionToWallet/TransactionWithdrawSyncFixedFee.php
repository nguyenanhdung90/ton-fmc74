<?php

namespace App\TON\Transactions\SyncTransactionToWallet;

class TransactionWithdrawSyncFixedFee extends TransactionToWalletAbstract
{
    public function getUpdateTransactionToWallet(): SyncTransactionInterface
    {
        return new UpdateWithdrawFixedFeeTransaction($this->transactionId);
    }
}
