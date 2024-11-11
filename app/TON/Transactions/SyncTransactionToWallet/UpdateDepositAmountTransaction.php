<?php

namespace App\TON\Transactions\SyncTransactionToWallet;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UpdateDepositAmountTransaction implements SyncTransactionInterface
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
            $transaction = DB::table('wallets_ton_transactions')
                ->where('id', $this->transactionId)
                ->lockForUpdate()
                ->first();
            if (!$transaction) {
                DB::rollBack();
                return;
            }
            if (empty($transaction->to_memo)) {
                DB::rollBack();
                return;
            }
            if ($transaction->is_sync_amount) {
                DB::rollBack();
                return;
            }
            if (empty($transaction->currency)) {
                DB::rollBack();
                return;
            }
            $wallet = DB::table('wallets_ton_address')
                ->where('currency', $transaction->currency)
                ->where('memo', $transaction->to_memo)
                ->lockForUpdate()
                ->first();
            if (!$wallet) {
                DB::rollBack();
                return;
            }
            $updateAmount = $wallet->amount + $transaction->amount;
            DB::table('wallets_ton_address')->where('id', $wallet->id)
                ->update(['amount' => $updateAmount, 'updated_at' => Carbon::now()]);
            DB::table('wallets_ton_transactions')->where('id', $this->transactionId)
                ->update(['is_sync_amount' => true, 'updated_at' => Carbon::now()]);
            printf("Update amount deposit tran id: %s, update amount: %s, currency: %s, to memo id: %s \n",
                $this->transactionId, $updateAmount, $transaction->currency, $wallet->id);
            DB::commit();
            return;
        } catch (\Exception $e) {
            DB::rollBack();
            return;
        }
    }
}
