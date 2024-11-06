<?php

namespace App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet;

class TransactionSyncFixedFeeWithdraw extends TransactionUpdateToWalletAbstract
{
    public function getUpdateTransactionToWallet(): SyncTransactionInterface
    {
        return new UpdateFixedFeeWithdrawTransaction($this->transactionId);
    }
}
