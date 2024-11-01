<?php

namespace App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet;

class TransactionExcess extends TransactionUpdateToWalletAbstract
{
    public function getUpdateTransactionToWallet(): UpdateAmountFeeTransactionInterface
    {
        return new UpdateExcessAmountFeeTransaction($this->transactionId);
    }
}
