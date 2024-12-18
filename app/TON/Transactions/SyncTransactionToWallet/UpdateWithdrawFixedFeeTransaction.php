<?php

namespace App\TON\Transactions\SyncTransactionToWallet;

use App\TON\TonHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UpdateWithdrawFixedFeeTransaction implements SyncTransactionInterface
{
    protected int $transactionId;

    public function __construct(int $transactionId)
    {
        $this->transactionId = $transactionId;
    }

    public function process(?array $data)
    {
        DB::beginTransaction();
        try {
            $transaction = DB::table('wallet_ton_transactions')
                ->where('id', $this->transactionId)
                ->lockForUpdate()
                ->first();
            if (!$transaction) {
                DB::rollBack();
                return;
            }
            if ($transaction->type !== TonHelper::WITHDRAW) {
                DB::rollBack();
                return;
            }
            if (empty($transaction->amount)) {
                DB::rollBack();
                return;
            }
            if ($transaction->is_sync_fixed_fee) {
                DB::rollBack();
                return;
            }
            if (empty($transaction->from_memo)) {
                DB::rollBack();
                return;
            }

            $walletMemo = DB::table('wallet_memos')->where('memo', $transaction->from_memo)->first();
            if (!$walletMemo) {
                DB::rollBack();
                return;
            }
            $wallet = DB::table('wallets')
                ->where('user_name', $walletMemo->user_name)
                ->where('currency', TonHelper::PAYN)
                ->where('is_active', TonHelper::ACTIVE)
                ->lockForUpdate()
                ->first();

            if (!$wallet) {
                DB::rollBack();
                return;
            }
            $remainingAmount = $wallet->amount - config("services.ton.fixed_fee");
            if ($remainingAmount < 0) {
                DB::rollBack();
                return;
            }
            DB::table('wallets')
                ->where('id', $wallet->id)
                ->update(['amount' => $remainingAmount, 'updated_at' => Carbon::now()]);
            DB::table('wallet_ton_transactions')
                ->where('id', $this->transactionId)
                ->update([
                    'is_sync_fixed_fee' => true,
                    'fixed_fee' => config('services.ton.fixed_fee'),
                    'updated_at' => Carbon::now()
                ]);
            DB::commit();
            return;
        } catch (\Exception $e) {
            DB::rollBack();
            return;
        }
    }

}
