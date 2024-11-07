<?php

namespace App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet;

use App\TON\Transactions\TransactionHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UpdateWithdrawFixedFeeTransaction implements SyncTransactionInterface
{
    protected int $transactionId;

    public function __construct(int $transactionId)
    {
        $this->transactionId = $transactionId;
    }

    public function process(array $data)
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
            if ($transaction->type !== TransactionHelper::WITHDRAW) {
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
            $wallet = DB::table('wallet_ton_memos')
                ->where('memo', $transaction->from_memo)
                ->where('currency', TransactionHelper::PAYN)
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
            DB::table('wallet_ton_memos')
                ->where('id', $wallet->id)
                ->update(['amount' => $remainingAmount, 'updated_at' => Carbon::now()]);
            DB::table('wallet_ton_transactions')
                ->where('id', $transaction->id)
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