<?php

namespace App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet;

class TransactionDepositOccur extends TransactionToWalletAbstract
{
    public function getUpdateTransactionToWallet(): SyncTransactionInterface
    {
        return new UpdateDepositOccurTransaction($this->transactionId);
    }
}
