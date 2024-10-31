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
            $walletTon = DB::table('wallet_ton_memos')
                ->where('currency', TransactionHelper::TON)
                ->where('memo', $this->transaction->from_memo)
                ->lockForUpdate()
                ->first();
            if ($walletTon) {
                $updateFee = $walletTon->amount - $this->transaction->total_fees;
                if ($updateFee >= 0) {
                    DB::table('wallet_ton_memos')->where('id', $walletTon->id)
                        ->update(['amount' => $updateFee, 'updated_at' => Carbon::now()]);
                    DB::table('wallet_ton_transactions')->where('id', $this->transaction->id)
                        ->update(['is_sync_total_fees' => true, 'updated_at' => Carbon::now()]);
                }
            }

            $walletJetton = DB::table('wallet_ton_memos')
                ->where('memo', $this->transaction->from_memo)
                ->where('currency', $this->transaction->currency)
                ->lockForUpdate()
                ->get(['id', 'memo', 'currency', 'amount'])
                ->first();
            if ($walletJetton) {
                $updateJettonAmount = $walletJetton->amount - $this->transaction->amount;
                if ($updateJettonAmount >= 0) {
                    DB::table('wallet_ton_memos')->where('id', $walletJetton->id)
                        ->update(['amount' => $updateJettonAmount, 'updated_at' => Carbon::now()]);

                    DB::table('wallet_ton_transactions')->where('id', $this->transaction->id)
                        ->update(['is_sync_amount' => true, 'updated_at' => Carbon::now()]);
                }
            }

            printf("Sync withdraw jetton id: %s, transfer amount: %s, to Ton memo: %s, memo id: %s \n",
                $this->transaction->id, $updateJettonAmount, $this->transaction->from_memo, $walletJetton->id);
            DB::commit();
            return;
        } catch (\Exception $e) {
            Log::info('Exception SyncWithdrawJetton: ' . $e->getMessage());
            DB::rollBack();
            return;
        }
    }
}
