<?php

namespace App\TON\Transactions\SyncAmountMemoWallet;

use App\TON\Transactions\TransactionHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SyncFixWithdrawJetton extends SyncMemoWalletAbstract
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
            if (!$this->transaction->is_sync_amount) {
                $wallet = DB::table('wallet_ton_memos')
                    ->where('currency', $this->transaction->currency)
                    ->where('memo', $this->transaction->from_memo)
                    ->lockForUpdate()
                    ->first();
                if ($wallet) {
                    $updateAmount = $wallet->amount - $this->transaction->amount;
                    if ($updateAmount >= 0) {
                        DB::table('wallet_ton_memos')->where('id', $wallet->id)
                            ->update(['amount' => $updateAmount, 'updated_at' => Carbon::now()]);
                        DB::table('wallet_ton_transactions')->where('id', $this->transaction->id)
                            ->update(['is_sync_amount' => true, 'updated_at' => Carbon::now()]);
                        printf("Sync amount withdraw tranid: %s \n", $this->transaction->id);
                    }
                }
            }

            if (!$this->transaction->is_sync_total_fees) {
                $walletTon = DB::table('wallet_ton_memos')
                    ->where('currency', TransactionHelper::TON)
                    ->where('memo', $this->transaction->from_memo)
                    ->lockForUpdate()
                    ->first();
                if ($walletTon) {
                    $updateFee = $walletTon->amount - $this->transaction->total_fees;
                    if ($updateFee >= 0) {
                        DB::table('wallet_ton_memos')->where('id', $wallet->id)
                            ->update(['amount' => $updateFee, 'updated_at' => Carbon::now()]);
                        DB::table('wallet_ton_transactions')->where('id', $this->transaction->id)
                            ->update(['is_sync_total_fees' => true, 'updated_at' => Carbon::now()]);
                        printf("Sync fee withdraw tranid: %s \n", $this->transaction->id);
                    }
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
