<?php

namespace App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet;

use App\TON\Transactions\TransactionHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UpdateExcessAmountFeeTransaction implements UpdateAmountFeeTransactionInterface
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
            if (!$transaction || empty($transaction->query_id)) {
                DB::rollBack();
                return;
            }
            if ($transaction->is_sync_amount && $transaction->is_sync_total_fees) {
                DB::rollBack();
                return;
            }
            $withdrawTran = DB::table('wallet_ton_transactions')
                ->where('query_id', $transaction->query_id)
                ->where('type', TransactionHelper::WITHDRAW)
                ->first();
            if (!$withdrawTran) {
                DB::rollBack();
                return;
            }
            if (empty($withdrawTran->from_memo)) {
                DB::rollBack();
                return;
            }

            $wallet = DB::table('wallet_ton_memos')
                ->where('currency', TransactionHelper::TON)
                ->where('memo', $withdrawTran->from_memo)
                ->lockForUpdate()
                ->first();
            if (!$wallet) {
                DB::rollBack();
                return;
            }

            if (!$transaction->is_sync_amount) {
                $updateAmount = $wallet->amount + $transaction->amount;
                DB::table('wallet_ton_memos')->where('id', $wallet->id)
                    ->update(['amount' => $updateAmount, 'updated_at' => Carbon::now()]);
                DB::table('wallet_ton_transactions')->where('id', $transaction->id)
                    ->update(['is_sync_amount' => true, 'updated_at' => Carbon::now()]);
                printf("Update excess amount tran id: %s, updated amount: %s, to memo id: %s  \n",
                    $transaction->id, $updateAmount, $wallet->id);
            }

            if (!$transaction->is_sync_total_fees) {
                if (!empty($updateAmount)) {
                    $updateFeeAmount = $updateAmount - $transaction->total_fees;
                } else {
                    $updateFeeAmount = $wallet->amount - $transaction->total_fees;
                }
                if ($updateFeeAmount >= 0) {
                    DB::table('wallet_ton_memos')->where('id', $wallet->id)
                        ->update(['amount' => $updateFeeAmount, 'updated_at' => Carbon::now()]);
                    DB::table('wallet_ton_transactions')->where('id', $transaction->id)
                        ->update(['is_sync_total_fees' => true, 'updated_at' => Carbon::now()]);
                    printf("Update excess fee tran id: %s, updated amount: %s, to memo id: %s \n",
                        $transaction->id, $updateFeeAmount, $wallet->id);
                }
            }

            DB::commit();
            return;
        } catch (\Exception $e) {
            DB::rollBack();
            return;
        }
    }
}
