<?php

namespace App\TON\Transactions\SyncTransactionToWallet;

use App\TON\TonHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UpdateWithdrawSuccessTransaction implements SyncTransactionInterface
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
            if (!$transaction || empty($transaction->from_memo) || empty($transaction->currency)) {
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
                ->where('currency', $transaction->currency)
                ->where('is_active', TonHelper::ACTIVE)
                ->first();

            if (!$wallet) {
                DB::rollBack();
                return;
            }

            DB::table('wallet_ton_transactions')
                ->where('id', $this->transactionId)
                ->update([
                    'status' => TonHelper::SUCCESS,
                    'updated_at' => Carbon::now()
                ]);
            DB::commit();
            printf("Update success withdraw tran id: %s, currency: %s \n",
                $this->transactionId, $transaction->currency);
            return;
        } catch (\Exception $e) {
            DB::rollBack();
            return;
        }
    }
}
