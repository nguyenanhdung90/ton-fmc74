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
                return;
            }
            $walletTon = DB::table('wallet_ton_memos')
                ->where('currency', $this->transaction->currency)
                ->where('memo', $this->transaction->to_memo)
                ->lockForUpdate()
                ->first();
            if (!$walletTon) {
                return;
            }
            if ($this->transaction->currency === TransactionHelper::TON) {
                $updateAmount = $walletTon->amount + ($this->transaction->amount - $this->transaction->total_fees);
                DB::table('wallet_ton_transactions')->where('id', $this->transaction->id)
                    ->update(['is_sync_amount_ton' => true, 'updated_at' => Carbon::now()]);
            } else {
                $updateAmount = $walletTon->amount + $this->transaction->amount;
                // process fee for jetton
                $walletTonMemo = DB::table('wallet_ton_memos')
                    ->where('memo', $this->transaction->to_memo)
                    ->where('currency', TransactionHelper::TON)
                    ->lockForUpdate()->get(['id', 'memo', 'currency', 'amount'])->first();
                if ($walletTonMemo && ($walletTonMemo->amount - $this->transaction->total_fees) > 0) {
                    $updateFeeTonAmount = $walletTonMemo->amount - $this->transaction->total_fees;
                    DB::table('wallet_ton_memos')->where('id', $walletTonMemo->id)
                        ->update(['amount' => $updateFeeTonAmount, 'updated_at' => Carbon::now()]);
                    DB::table('wallet_ton_transactions')->where('id', $this->transaction->id)
                        ->update(['is_sync_amount_ton' => true, 'updated_at' => Carbon::now()]);
                }
                DB::table('wallet_ton_transactions')->where('id', $this->transaction->id)
                    ->update(['is_sync_amount_jetton' => true, 'updated_at' => Carbon::now()]);
            }
            DB::table('wallet_ton_memos')->where('id', $walletTon->id)
                ->update(['amount' => $updateAmount, 'updated_at' => Carbon::now()]);

            printf("Sync withdraw ton id: %s, transfer amount: %s, to Ton memo: %s, memo id: %s", $this->transaction->id,
                $updateAmount, $this->transaction->to_memo, $walletTon->id);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }
    }
}
