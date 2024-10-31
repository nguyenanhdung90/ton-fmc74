<?php

namespace App\TON\Transactions\SyncAmountMemoWallet;


use App\TON\Transactions\TransactionHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SyncFixDepositJetton extends SyncMemoWalletAbstract
{
    public function process(): void
    {
        if (empty($this->transaction->to_memo)) {
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
                    ->where('memo', $this->transaction->to_memo)
                    ->lockForUpdate()
                    ->first();
                if ($wallet) {
                    $updateAmount = $wallet->amount + $this->transaction->amount;
                    DB::table('wallet_ton_memos')->where('id', $wallet->id)
                        ->update(['amount' => $updateAmount, 'updated_at' => Carbon::now()]);
                    DB::table('wallet_ton_transactions')->where('id', $this->transaction->id)
                        ->update(['is_sync_amount' => true, 'updated_at' => Carbon::now()]);
                    printf("Sync amount deposit tranid: %s \n", $this->transaction->id);
                }
            }

            if (!$this->transaction->is_sync_total_fees) {
                $tonWallet = DB::table('wallet_ton_memos')
                    ->where('currency', TransactionHelper::TON)
                    ->where('memo', $this->transaction->to_memo)
                    ->lockForUpdate()
                    ->first();
                if ($tonWallet) {
                    $updateFee = $tonWallet->amount - $this->transaction->total_fees;
                    if ($updateFee >= 0) {
                        DB::table('wallet_ton_memos')->where('id', $tonWallet->id)
                            ->update(['amount' => $updateFee, 'updated_at' => Carbon::now()]);
                        DB::table('wallet_ton_transactions')->where('id', $this->transaction->id)
                            ->update(['is_sync_total_fees' => true, 'updated_at' => Carbon::now()]);
                        printf("Sync fee deposit tranid: %s \n", $this->transaction->id);
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
