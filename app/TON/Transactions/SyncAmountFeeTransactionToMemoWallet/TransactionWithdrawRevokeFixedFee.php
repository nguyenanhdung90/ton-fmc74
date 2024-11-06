<?php

namespace App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet;

class TransactionWithdrawRevokeFixedFee extends TransactionToWalletAbstract
{
    public function getUpdateTransactionToWallet(): SyncTransactionInterface
    {
        return new RevokeWithdrawFixedFeeTransaction($this->transactionId);
    }
}
