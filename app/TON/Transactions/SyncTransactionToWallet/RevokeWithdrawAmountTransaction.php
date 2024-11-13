<?php

namespace App\TON\Transactions\SyncTransactionToWallet;

use App\TON\TonHelper;
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
            if (empty($transaction->from_memo)) {
                DB::rollBack();
                return;
            }
            if (empty($transaction->currency)) {
                DB::rollBack();
                return;
            }
            if (!$transaction->is_sync_amount) {
                DB::rollBack();
                return;
            }

            $wallet = DB::table('wallets')
                ->leftJoin('wallet_memos', 'wallets.user_name', '=', 'wallet_memos.user_name')
                ->where('wallets.currency', $transaction->currency)
                ->where('wallet_memos.memo', $transaction->from_memo)
                ->lockForUpdate()
                ->select('wallet.*')
                ->first();
            if (!$wallet) {
                DB::rollBack();
                return;
            }

            DB::table('wallet_ton_transactions')
                ->where('id', $this->transactionId)
                ->update([
                    'lt' => Arr::get($data, 'lt'),
                    'hash' => Arr::get($data, 'hash'),
                    'status' => TonHelper::FAILED,
                    'is_sync_amount' => false,
                    'updated_at' => Carbon::now()
                ]);
            $updateAmount = $wallet->amount + $transaction->amount;
            DB::table('wallets')->where('id', $wallet->id)
                ->update(['amount' => $updateAmount, 'updated_at' => Carbon::now()]);
            printf("Revoke amount withdraw tran id: %s, update amount: %s, currency: %s, to memo id: %s \n",
                $this->transactionId, $updateAmount, $transaction->currency, $wallet->id);
            DB::commit();
            return;
        } catch (\Exception $e) {
            DB::rollBack();
            return;
        }
    }
}
