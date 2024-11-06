<?php

namespace App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet;

class TransactionDepositFee extends TransactionUpdateToWalletAbstract
{
    public function getUpdateTransactionToWallet(): SyncTransactionInterface
    {
        return new UpdateDepositFeeTransaction($this->transactionId);
    }
}
