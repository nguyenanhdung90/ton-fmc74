<?php

namespace App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet;

class TransactionRevokeFixedFeeWithdraw extends TransactionToWalletAbstract
{
    public function getUpdateTransactionToWallet(): SyncTransactionInterface
    {
        return new RevokeFixedFeeWithdrawTransaction($this->transactionId);
    }
}
