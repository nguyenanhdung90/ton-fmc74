<?php

namespace App\TON\Transactions\SyncAmountMemoWallet;

use App\TON\Transactions\TransactionHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SyncDeposit extends SyncMemoWalletAbstract
{
    public function process(): void
    {
        DB::beginTransaction();
        try {
            if (empty($this->transaction->to_memo)) {
                DB::rollBack();
                return;
            }
            $walletMemo = DB::table('wallet_ton_memos')
                ->where('currency', $this->transaction->currency)
                ->where('memo', $this->transaction->to_memo)
                ->lockForUpdate()
                ->first();
            if (!$walletMemo) {
                DB::rollBack();
                return;
            }
            if ($this->transaction->currency === TransactionHelper::TON) {
                $updateAmount = $walletMemo->amount + ($this->transaction->amount - $this->transaction->total_fees);
                DB::table('wallet_ton_transactions')->where('id', $this->transaction->id)
                    ->update(['is_sync_amount' => true, 'is_sync_total_fees' => true, 'updated_at' => Carbon::now()]);
            } else {
                $updateAmount = $walletMemo->amount + $this->transaction->amount;
                // process fee
                $walletTon = DB::table('wallet_ton_memos')
                    ->where('memo', $this->transaction->to_memo)
                    ->where('currency', TransactionHelper::TON)
                    ->lockForUpdate()
                    ->get(['id', 'memo', 'currency', 'amount'])
                    ->first();
                $updateFee = $walletTon->amount - $this->transaction->total_fees;
                if ($walletTon && $updateFee > 0) {
                    DB::table('wallet_ton_memos')->where('id', $walletTon->id)
                        ->update(['amount' => $updateFee, 'updated_at' => Carbon::now()]);
                    DB::table('wallet_ton_transactions')->where('id', $this->transaction->id)
                        ->update(['is_sync_total_fees' => true, 'updated_at' => Carbon::now()]);
                }
                // end fee
                DB::table('wallet_ton_transactions')->where('id', $this->transaction->id)
                    ->update(['is_sync_amount' => true, 'updated_at' => Carbon::now()]);
            }
            DB::table('wallet_ton_memos')->where('id', $walletMemo->id)
                ->update(['amount' => $updateAmount, 'updated_at' => Carbon::now()]);

            printf("Sync withdraw ton id: %s, to Ton memo: %s, memo id: %s, updated amount: %s \n",
                $this->transaction->id, $this->transaction->to_memo, $walletMemo->id, $updateAmount);
            DB::commit();
            return;
        } catch (\Exception $e) {
            DB::rollBack();
            return;
        }
    }
}
