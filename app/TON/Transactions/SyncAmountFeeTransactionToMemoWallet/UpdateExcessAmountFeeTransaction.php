<?php

namespace App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet;

use App\Models\WalletTonTransaction;
use App\TON\Transactions\TransactionHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UpdateExcessAmountFeeTransaction implements UpdateAmountFeeTransactionInterface
{
    protected WalletTonTransaction $transaction;

    public function __construct(WalletTonTransaction $transaction)
    {
        $this->transaction = $transaction;
    }

    public function process()
    {
        if (empty($this->transaction->query_id)) {
            return;
        }
        $withdrawTran = WalletTonTransaction::where('query_id', $this->transaction->query_id)
            ->where('type', TransactionHelper::WITHDRAW)
            ->first();
        if (!$withdrawTran) {
            return;
        }
        if (empty($withdrawTran->from_memo)) {
            return;
        }
        if ($this->transaction->is_sync_amount && $this->transaction->is_sync_total_fees) {
            return;
        }
        DB::beginTransaction();
        try {
            $wallet = DB::table('wallet_ton_memos')
                ->where('currency', TransactionHelper::TON)
                ->where('memo', $withdrawTran->from_memo)
                ->lockForUpdate()
                ->first();
            if (!$wallet) {
                DB::rollBack();
                return;
            }

            if (!$this->transaction->is_sync_amount) {
                $updateAmount = $wallet->amount + $this->transaction->amount;
                DB::table('wallet_ton_memos')->where('id', $wallet->id)
                    ->update(['amount' => $updateAmount, 'updated_at' => Carbon::now()]);
                DB::table('wallet_ton_transactions')->where('id', $this->transaction->id)
                    ->update(['is_sync_amount' => true, 'updated_at' => Carbon::now()]);
                printf("Sync excess amount tran id: %s, updated amount: %s, to memo id: %s  \n",
                    $this->transaction->id, $updateAmount, $wallet->id);
            }

            if (!$this->transaction->is_sync_total_fees) {
                if (!empty($updateAmount)) {
                    $updateFeeAmount = $updateAmount - $this->transaction->total_fees;
                } else {
                    $updateFeeAmount = $wallet->amount - $this->transaction->total_fees;
                }
                if ($updateFeeAmount >= 0) {
                    DB::table('wallet_ton_memos')->where('id', $wallet->id)
                        ->update(['amount' => $updateFeeAmount, 'updated_at' => Carbon::now()]);
                    DB::table('wallet_ton_transactions')->where('id', $this->transaction->id)
                        ->update(['is_sync_total_fees' => true, 'updated_at' => Carbon::now()]);
                    printf("Update excess fee tran id: %s, updated amount: %s, to memo id: %s \n",
                        $this->transaction->id, $updateFeeAmount, $wallet->id);
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
