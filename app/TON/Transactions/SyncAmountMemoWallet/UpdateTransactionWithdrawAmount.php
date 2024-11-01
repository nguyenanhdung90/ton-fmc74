<?php

namespace App\TON\Transactions\SyncAmountMemoWallet;

use App\TON\Transactions\TransactionHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UpdateTransactionWithdrawAmount extends SyncMemoWalletAbstract
{
    public function process(): void
    {
        if (empty($this->transaction->from_memo) || $this->transaction->is_sync_amount
            || empty($this->transaction->currency)) {
            return;
        }
        DB::beginTransaction();
        try {
            $wallet = DB::table('wallet_ton_memos')
                ->where('currency', $this->transaction->currency)
                ->where('memo', $this->transaction->from_memo)
                ->lockForUpdate()
                ->first();
            if (!$wallet) {
                DB::rollBack();
                return;
            }
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
                printf("Sync amount withdraw tran id: %s, update amount: %s \n", $this->transaction->id, $updateAmount);
            }
            DB::commit();
            return;
        } catch (\Exception $e) {
            DB::rollBack();
            return;
        }
    }
}
