<?php

namespace App\TON\Transactions\SyncAmountMemoWallet;

use App\Models\WalletTonTransaction;
use App\TON\Transactions\TransactionHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UpdateTransactionExcess extends SyncMemoWalletAbstract
{
    public function process(): void
    {
        if (empty($this->transaction->query_id)) {
            return;
        }
        $withdrawTran = WalletTonTransaction::where('query_id', $this->transaction->query_id)
            ->where('type', TransactionHelper::WITHDRAW)
            ->first();
        if (!$withdrawTran) {
            return;
        }
        if (empty($withdrawTran->from_memo)) {
            return;
        }
        if ($this->transaction->is_sync_amount && $this->transaction->is_sync_total_fees) {
            return;
        }
        DB::beginTransaction();
        try {
            $walletTon = DB::table('wallet_ton_memos')
                ->where('currency', TransactionHelper::TON)
                ->where('memo', $withdrawTran->from_memo)
                ->lockForUpdate()
                ->first();
            if (!$walletTon) {
                DB::rollBack();
                return;
            }

            if (!$this->transaction->is_sync_amount) {
                $updateAmount = $walletTon->amount + $this->transaction->amount;
                DB::table('wallet_ton_memos')->where('id', $walletTon->id)
                    ->update(['amount' => $updateAmount, 'updated_at' => Carbon::now()]);
                DB::table('wallet_ton_transactions')->where('id', $this->transaction->id)
                    ->update(['is_sync_amount' => true, 'updated_at' => Carbon::now()]);
                printf("Sync excess amount tran id: %s, updated amount: %s \n",
                    $this->transaction->id, $updateAmount);
            }

            if (!$this->transaction->is_sync_total_fees) {
                if (!empty($updateAmount)) {
                    $updateFeeAmount = $updateAmount - $this->transaction->total_fees;
                } else {
                    $updateFeeAmount = $walletTon->amount - $this->transaction->total_fees;
                }
                if ($updateFeeAmount >= 0) {
                    DB::table('wallet_ton_memos')->where('id', $walletTon->id)
                        ->update(['amount' => $updateFeeAmount, 'updated_at' => Carbon::now()]);
                    DB::table('wallet_ton_transactions')->where('id', $this->transaction->id)
                        ->update(['is_sync_total_fees' => true, 'updated_at' => Carbon::now()]);
                    printf("Update excess fee tran id: %s, updated amount: %s \n",
                        $this->transaction->id, $updateFeeAmount);
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
