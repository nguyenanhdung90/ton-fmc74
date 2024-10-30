<?php

namespace App\TON\Transactions\SyncAmountMemoWallet;

use App\Models\WalletTonMemo;
use App\Models\WalletTonTransaction;
use App\TON\Transactions\TransactionHelper;
use Illuminate\Support\Facades\Log;

class SyncTransactionExcess extends SyncMemoWalletAbstract
{
    public function process(): void
    {
        try {
            if (empty($this->transaction->query_id)) {
                return;
            }
            $withdraw = WalletTonTransaction::where('query_id', $this->transaction->query_id)
                ->where('type', TransactionHelper::WITHDRAW)
                ->where('currency', $this->transaction->currency)
                ->first();
            if (!$withdraw) {
                return;
            }
            if (empty($withdraw->from_memo)) {
                return;
            }
            $transferAmount = $withdraw->amount - $withdraw->total_fees;
            if ($transferAmount <= 0) {
                return;
            }
            $walletTon = WalletTonMemo::where('currency', TransactionHelper::TON)
                ->where('memo', $withdraw->from_memo)
                ->first();
            if (!$walletTon) {
                return;
            }
            $updateAmount = $walletTon->amount + $transferAmount;
            WalletTonMemo::where('id', $walletTon->id)->updated(['amount' => $updateAmount]);
            printf("Sync excess id: %s, transfer amount: %s, to Ton memo: %s, memo id: %s", $withdraw->id,
                $transferAmount, $withdraw->from_memo, $walletTon->id);
        } catch (\Exception $e) {
            printf("SyncTransactionExcess: " . $e->getMessage() . "\n");
            Log::error("SyncTransactionExcess: " . $e->getMessage());
        }
    }
}
