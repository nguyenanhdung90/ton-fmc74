<?php

namespace App\TON\Transactions\SyncAmountMemoWallet;

use App\TON\Transactions\TransactionHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncWithdrawJetton extends SyncMemoWalletAbstract
{
    public function process(): void
    {
        DB::beginTransaction();
        try {
            if (empty($this->transaction->from_memo)) {
                DB::rollBack();
                return;
            }
            $walletTonMemo = DB::table('wallet_ton_memos')
                ->where('currency', TransactionHelper::TON)
                ->where('memo', $this->transaction->from_memo)
                ->lockForUpdate()
                ->first();
            if ($walletTonMemo) {
                $updateAmount = $walletTonMemo->amount - $this->transaction->total_fees;
                if ($updateAmount >= 0) {
                    DB::table('wallet_ton_memos')->where('id', $walletTonMemo->id)
                        ->update(['amount' => $updateAmount, 'updated_at' => Carbon::now()]);
                    DB::table('wallet_ton_transactions')->where('id', $this->transaction->id)
                        ->update(['is_sync_total_fees' => true, 'updated_at' => Carbon::now()]);
                }
            }

            $walletJettonMemo = DB::table('wallet_ton_memos')
                ->where('memo', $this->transaction->from_memo)
                ->where('currency', $this->transaction->currency)
                ->lockForUpdate()
                ->get(['id', 'memo', 'currency', 'amount'])
                ->first();
            if ($walletJettonMemo) {
                $updateJettonAmount = $walletJettonMemo->amount - $this->transaction->amount;
                if ($updateJettonAmount >= 0) {
                    DB::table('wallet_ton_memos')->where('id', $walletJettonMemo->id)
                        ->update(['amount' => $updateJettonAmount, 'updated_at' => Carbon::now()]);

                    DB::table('wallet_ton_transactions')->where('id', $this->transaction->id)
                        ->update(['is_sync_amount' => true, 'updated_at' => Carbon::now()]);
                }
            }

            printf("Sync withdraw jetton id: %s, transfer amount: %s, to Ton memo: %s, memo id: %s",
                $this->transaction->id, $updateJettonAmount, $this->transaction->from_memo, $walletJettonMemo->id);
            DB::commit();
            return;
        } catch (\Exception $e) {
            Log::info('Exception SyncWithdrawJetton: ' . $e->getMessage());
            DB::rollBack();
            return;
        }
    }
}
