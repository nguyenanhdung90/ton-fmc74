<?php

namespace App\TON\Transactions\SyncTransactionToWallet;

use App\TON\TonHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UpdateDepositOccurTransaction implements SyncTransactionInterface
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
            if (empty($transaction->to_memo)) {
                DB::rollBack();
                return;
            }
            if ($transaction->is_sync_occur_ton) {
                DB::rollBack();
                return;
            }
            $walletMemo = DB::table('wallet_memos')->where('memo', $transaction->to_memo)->first();
            if (!$walletMemo) {
                DB::rollBack();
                return;
            }
            $wallet = DB::table('wallets')
                ->where('user_name', $walletMemo->user_name)
                ->where('currency', TonHelper::TON)
                ->where('is_active', TonHelper::ACTIVE)
                ->lockForUpdate()
                ->first();
            if (!$wallet) {
                DB::rollBack();
                return;
            }
            $updateFeeAmount = $wallet->amount - $transaction->occur_ton;
            if ($updateFeeAmount >= 0) {
                DB::table('wallets')->where('id', $wallet->id)
                    ->update(['amount' => $updateFeeAmount, 'updated_at' => Carbon::now()]);
                DB::table('wallet_ton_transactions')->where('id', $this->transactionId)
                    ->update(['is_sync_occur_ton' => true, 'updated_at' => Carbon::now()]);
                printf("Update occur deposit tran id: %s, updateFeeAmount: %s, to memo id: %s \n",
                    $this->transactionId, $updateFeeAmount, $wallet->id);
            }
            DB::commit();
            return;
        } catch (\Exception $e) {
            DB::rollBack();
            return;
        }
    }
}
