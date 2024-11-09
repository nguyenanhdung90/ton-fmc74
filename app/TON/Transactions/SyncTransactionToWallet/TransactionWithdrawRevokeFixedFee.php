<?php

namespace App\TON\Transactions\SyncTransactionToWallet;

class TransactionWithdrawRevokeFixedFee extends TransactionToWalletAbstract
{
    public function getUpdateTransactionToWallet(): SyncTransactionInterface
    {
        return new RevokeWithdrawFixedFeeTransaction($this->transactionId);
    }
}
