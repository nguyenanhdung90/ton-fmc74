<?php

namespace App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet;

class TransactionDepositFee extends TransactionUpdateToWalletAbstract
{
    public function getUpdateTransactionToWallet(): UpdateAmountFeeTransactionInterface
    {
        return new UpdateDepositFeeTransaction($this->transaction);
    }
}
