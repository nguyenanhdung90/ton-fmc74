<?php

namespace App\TON\Transactions\SyncAmountMemoWallet;

use App\TON\Transactions\TransactionHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncTransactionExcess extends SyncMemoWalletAbstract
{
    public function process(): void
    {
        DB::beginTransaction();
        try {
            if (empty($this->transaction->query_id)) {
                return;
            }
            $withdraw = DB::table('wallet_ton_transactions')
                ->where('query_id', $this->transaction->query_id)
                ->where('type', TransactionHelper::WITHDRAW)
                ->where('currency', $this->transaction->currency)
                ->first();
            if (!$withdraw) {
                return;
            }
            if (empty($withdraw->from_memo)) {
                return;
            }
            $transferAmount = $this->transaction->amount - $this->transaction->total_fees;
            if ($transferAmount <= 0) {
                return;
            }
            $walletTon = DB::table('wallet_ton_memos')
                ->where('currency', TransactionHelper::TON)
                ->where('memo', $withdraw->from_memo)
                ->lockForUpdate()
                ->first();
            if (!$walletTon) {
                return;
            }
            $updateAmount = $walletTon->amount + $transferAmount;
            DB::table('wallet_ton_memos')->where('id', $walletTon->id)
                ->update(['amount' => $updateAmount, 'updated_at' => Carbon::now()]);
            DB::table('wallet_ton_transactions')->where('id', $this->transaction->id)
                ->update(['is_sync_amount_ton' => true, 'updated_at' => Carbon::now()]);
            printf("Sync excess id: %s, transfer amount: %s, to Ton memo: %s, memo id: %s", $withdraw->id,
                $transferAmount, $withdraw->from_memo, $walletTon->id);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            printf("SyncTransactionExcess: " . $e->getMessage() . "\n");
            Log::error("SyncTransactionExcess: " . $e->getMessage());
        }
    }
}
