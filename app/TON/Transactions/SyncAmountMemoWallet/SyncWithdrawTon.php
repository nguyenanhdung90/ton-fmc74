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
                DB::rollBack();
                return;
            }
            $walletTon = DB::table('wallet_ton_memos')
                ->where('currency', TransactionHelper::TON)
                ->where('memo', $this->transaction->from_memo)
                ->lockForUpdate()
                ->first();
            if (!$walletTon) {
                DB::rollBack();
                return;
            }
            if ($walletTon->amount < $this->transaction->amount) {
                DB::rollBack();
                return;
            }
            $updateAmount = $walletTon->amount - $this->transaction->amount - $this->transaction->total_fees;
            if ($updateAmount < 0) {
                $updateAmount = $walletTon->amount - $this->transaction->amount;
                DB::table('wallet_ton_transactions')->where('id', $this->transaction->id)
                    ->update(['is_sync_amount' => true, 'updated_at' => Carbon::now()]);
            } else {
                DB::table('wallet_ton_transactions')->where('id', $this->transaction->id)
                    ->update(['is_sync_amount' => true, 'is_sync_total_fees' => true, 'updated_at' => Carbon::now()]);
            }

            DB::table('wallet_ton_memos')->where('id', $walletTon->id)
                ->update(['amount' => $updateAmount, 'updated_at' => Carbon::now()]);

            printf("Sync withdraw ton id: %s, transfer amount: %s, to Ton memo: %s, memo id: %s \n",
                $this->transaction->id,
                $updateAmount, $this->transaction->from_memo, $walletTon->id);
            DB::commit();
            return;
        } catch (\Exception $e) {
            DB::rollBack();
            return;
        }
    }
}
