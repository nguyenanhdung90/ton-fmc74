<?php

namespace App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet;

class TransactionDepositAmount extends TransactionUpdateToWalletAbstract
{
    public function getUpdateTransactionToWallet(): UpdateAmountFeeTransactionInterface
    {
        return new UpdateDepositAmountTransaction($this->transactionId);
    }
}
