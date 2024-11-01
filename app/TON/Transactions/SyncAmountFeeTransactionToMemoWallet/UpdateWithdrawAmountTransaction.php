<?php

namespace App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UpdateWithdrawAmountTransaction implements UpdateAmountFeeTransactionInterface
{
    protected int $transactionId;

    public function __construct(int $transactionId)
    {
        $this->transactionId = $transactionId;
    }

    public function process()
    {
        DB::beginTransaction();
        try {
            $transaction = DB::table('wallet_ton_transactions')
                ->where('id', $this->transactionId)
                ->lockForUpdate()
                ->first();
            if (!$transaction || empty($transaction->from_memo) || $transaction->is_sync_amount
                || empty($transaction->currency)) {
                DB::rollBack();
                return;
            }

            $wallet = DB::table('wallet_ton_memos')
                ->where('currency', $transaction->currency)
                ->where('memo', $transaction->from_memo)
                ->lockForUpdate()
                ->first();
            if (!$wallet) {
                DB::rollBack();
                return;
            }
            $updateAmount = $wallet->amount - $transaction->amount;
            if ($updateAmount >= 0) {
                DB::table('wallet_ton_memos')->where('id', $wallet->id)
                    ->update(['amount' => $updateAmount, 'updated_at' => Carbon::now()]);
                DB::table('wallet_ton_transactions')->where('id', $transaction->id)
                    ->update(['is_sync_amount' => true, 'updated_at' => Carbon::now()]);
                printf("Update amount withdraw tran id: %s, update amount: %s, currency: %s, to memo id: %s \n",
                    $transaction->id, $updateAmount, $transaction->currency, $wallet->id);
            }
            DB::commit();
            return;
        } catch (\Exception $e) {
            DB::rollBack();
            return;
        }
    }
}
