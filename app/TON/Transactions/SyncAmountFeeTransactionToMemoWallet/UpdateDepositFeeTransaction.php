<?php

namespace App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet;

use App\TON\Transactions\TransactionHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UpdateDepositFeeTransaction implements UpdateAmountFeeTransactionInterface
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
            if (!$transaction || empty($transaction->to_memo) || $transaction->is_sync_occur_ton) {
                DB::rollBack();
                return;
            }
            $wallet = DB::table('wallet_ton_memos')
                ->where('currency', TransactionHelper::TON)
                ->where('memo', $transaction->to_memo)
                ->lockForUpdate()
                ->first();
            if (!$wallet) {
                DB::rollBack();
                return;
            }
            $updateFeeAmount = $wallet->amount - $transaction->occur_ton;
            if ($updateFeeAmount >= 0) {
                DB::table('wallet_ton_memos')->where('id', $wallet->id)
                    ->update(['amount' => $updateFeeAmount, 'updated_at' => Carbon::now()]);
                DB::table('wallet_ton_transactions')->where('id', $transaction->id)
                    ->update(['is_sync_occur_ton' => true, 'updated_at' => Carbon::now()]);
                printf("Update fee deposit tran id: %s, updateFeeAmount: %s, to memo id: %s \n",
                    $transaction->id, $updateFeeAmount, $wallet->id);
            }
            DB::commit();
            return;
        } catch (\Exception $e) {
            DB::rollBack();
            return;
        }
    }
}
