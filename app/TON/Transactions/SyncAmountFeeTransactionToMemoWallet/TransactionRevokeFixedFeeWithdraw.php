<?php

namespace App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet;

class TransactionRevokeFixedFeeWithdraw extends TransactionUpdateToWalletAbstract
{
    public function getUpdateTransactionToWallet(): SyncTransactionInterface
    {
        return new RevokeFixedFeeWithdrawTransaction($this->transactionId);
    }
}
