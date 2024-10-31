<?php

namespace App\TON\Transactions\SyncAmountMemoWallet;

use App\TON\Transactions\TransactionHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SyncFixWithdrawTon extends SyncMemoWalletAbstract
{
    public function process(): void
    {
        if (empty($this->transaction->from_memo)) {
            return;
        }
        if ($this->transaction->is_sync_amount && $this->transaction->is_sync_total_fees) {
            return;
        }
        DB::beginTransaction();
        try {
            $wallet = DB::table('wallet_ton_memos')
                ->where('currency', TransactionHelper::TON)
                ->where('memo', $this->transaction->from_memo)
                ->lockForUpdate()
                ->first();
            if (!$wallet) {
                DB::rollBack();
                return;
            }
            if (!$this->transaction->is_sync_amount) {
                $wallet = DB::table('wallet_ton_memos')
                    ->where('currency', TransactionHelper::TON)
                    ->where('memo', $this->transaction->from_memo)
                    ->lockForUpdate()
                    ->first();
                $updateAmount = $wallet->amount - $this->transaction->amount;
                if ($updateAmount >= 0) {
                    DB::table('wallet_ton_memos')->where('id', $wallet->id)
                        ->update(['amount' => $updateAmount, 'updated_at' => Carbon::now()]);
                    DB::table('wallet_ton_transactions')->where('id', $this->transaction->id)
                        ->update(['is_sync_amount' => true, 'updated_at' => Carbon::now()]);
                    printf("Sync amount withdraw tranid: %s \n", $this->transaction->id);
                }
            }

            if (!$this->transaction->is_sync_total_fees) {
                if (!empty($updateAmount)) {
                    $updateFee = $updateAmount - $this->transaction->total_fees;
                } else {
                    $updateFee = $wallet->amount - $this->transaction->total_fees;
                }

                if ($updateFee >= 0) {
                    DB::table('wallet_ton_memos')->where('id', $wallet->id)
                        ->update(['amount' => $updateFee, 'updated_at' => Carbon::now()]);
                    DB::table('wallet_ton_transactions')->where('id', $this->transaction->id)
                        ->update(['is_sync_total_fees' => true, 'updated_at' => Carbon::now()]);
                    printf("Sync fee withdraw tranid: %s \n", $this->transaction->id);
                }
            }

            DB::commit();
            return;
        } catch (\Exception $e) {
            DB::rollBack();
            return;
        }
    }
}
