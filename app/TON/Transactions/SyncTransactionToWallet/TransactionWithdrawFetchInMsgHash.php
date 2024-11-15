<?php

namespace App\TON\Transactions\SyncTransactionToWallet;

class TransactionWithdrawFetchInMsgHash extends TransactionToWalletAbstract
{
    public function getUpdateTransactionToWallet(): SyncTransactionInterface
    {
        return new FetchWithdrawInMsgHashTransaction($this->transactionId);
    }
}
