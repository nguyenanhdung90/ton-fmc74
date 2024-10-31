<?php

namespace App\TON\Transactions\SyncAmountMemoWallet;


use App\TON\Transactions\TransactionHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SyncFixDepositTon extends SyncMemoWalletAbstract
{
    public function process(): void
    {
        if (empty($this->transaction->to_memo)) {
            return;
        }
        if ($this->transaction->is_sync_amount && $this->transaction->is_sync_total_fees) {
            return;
        }
        $tonWallet = DB::table('wallet_ton_memos')
            ->where('currency', TransactionHelper::TON)
            ->where('memo', $this->transaction->to_memo)
            ->lockForUpdate()
            ->first();
        if (!$tonWallet) {
            return;
        }
        DB::beginTransaction();
        try {
            if (!$this->transaction->is_sync_amount) {
                $updateAmount = $tonWallet->amount + $this->transaction->amount;
                DB::table('wallet_ton_memos')->where('id', $tonWallet->id)
                    ->update(['amount' => $updateAmount, 'updated_at' => Carbon::now()]);
                DB::table('wallet_ton_transactions')->where('id', $this->transaction->id)
                    ->update(['is_sync_amount' => true, 'updated_at' => Carbon::now()]);
                printf("Sync amount deposit tranid: %s \n", $this->transaction->id);
            }

            if (!$this->transaction->is_sync_total_fees) {
                if (!empty($updateAmount)) {
                    $updateFee = $updateAmount - $this->transaction->total_fees;
                } else {
                    $updateFee = $tonWallet->amount - $this->transaction->total_fees;
                }
                if ($updateFee >= 0) {
                    DB::table('wallet_ton_memos')->where('id', $tonWallet->id)
                        ->update(['amount' => $updateFee, 'updated_at' => Carbon::now()]);
                    DB::table('wallet_ton_transactions')->where('id', $this->transaction->id)
                        ->update(['is_sync_total_fees' => true, 'updated_at' => Carbon::now()]);
                    printf("Sync fee deposit tranid: %s \n", $this->transaction->id);
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
