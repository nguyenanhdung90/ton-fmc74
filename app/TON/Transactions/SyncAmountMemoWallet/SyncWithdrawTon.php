<?php

namespace App\TON\Transactions\SyncAmountMemoWallet;

use App\TON\Transactions\TransactionHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SyncWithdrawTon extends SyncMemoWalletAbstract
{
    public function process(): void
    {
        DB::beginTransaction();
        try {
            if (empty($this->transaction->from_memo)) {
                return;
            }
            $walletTon = DB::table('wallet_ton_memos')
                ->where('currency', TransactionHelper::TON)
                ->where('memo', $this->transaction->from_memo)
                ->lockForUpdate()
                ->first();
            if (!$walletTon) {
                return;
            }
            $updateAmount = $walletTon->amount - ($this->transaction->amount + $this->transaction->total_fees);
            if ($updateAmount < 0) {
                return;
            }
            DB::table('wallet_ton_memos')->where('id', $walletTon->id)
                ->update(['amount' => $updateAmount, 'updated_at' => Carbon::now()]);
            DB::table('wallet_ton_transactions')->where('id', $this->transaction->id)
                ->update(['is_sync_amount_ton' => true, 'updated_at' => Carbon::now()]);
            printf("Sync withdraw ton id: %s, transfer amount: %s, to Ton memo: %s, memo id: %s", $this->transaction->id,
                $updateAmount, $this->transaction->from_memo, $walletTon->id);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }
    }
}
