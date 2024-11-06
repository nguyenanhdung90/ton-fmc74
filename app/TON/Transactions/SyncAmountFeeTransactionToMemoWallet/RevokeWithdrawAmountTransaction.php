<?php

namespace App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet;

use App\TON\Transactions\TransactionHelper;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class RevokeWithdrawAmountTransaction implements SyncTransactionInterface
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
            if (empty($transaction->from_memo)) {
                DB::rollBack();
                return;
            }
            if (empty($transaction->currency)) {
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

            DB::table('wallet_ton_transactions')
                ->where('id', $transaction->id)
                ->update([
                    'lt' => Arr::get($data, 'lt'),
                    'hash' => Arr::get($data, 'hash'),
                    'status' => TransactionHelper::FAILED,
                    'is_sync_amount' => false,
                    'updated_at' => Carbon::now()
                ]);
            $updateAmount = $wallet->amount + $transaction->amount;
            DB::table('wallet_ton_memos')->where('id', $wallet->id)
                ->update(['amount' => $updateAmount, 'updated_at' => Carbon::now()]);
            printf("Update amount withdraw tran id: %s, update amount: %s, currency: %s, to memo id: %s \n",
                $transaction->id, $updateAmount, $transaction->currency, $wallet->id);
            DB::commit();
            return;
        } catch (\Exception $e) {
            DB::rollBack();
            return;
        }
    }
}
