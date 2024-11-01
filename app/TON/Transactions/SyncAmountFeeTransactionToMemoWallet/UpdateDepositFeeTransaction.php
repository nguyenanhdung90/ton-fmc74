<?php

namespace App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet;

use App\Models\WalletTonTransaction;
use App\TON\Transactions\TransactionHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UpdateDepositFeeTransaction implements UpdateAmountFeeTransactionInterface
{
    protected WalletTonTransaction $transaction;

    public function __construct(WalletTonTransaction $transaction)
    {
        $this->transaction = $transaction;
    }

    public function process()
    {
        if (empty($this->transaction->to_memo) || $this->transaction->is_sync_total_fees) {
            return;
        }
        DB::beginTransaction();
        try {
            $wallet = DB::table('wallet_ton_memos')
                ->where('currency', TransactionHelper::TON)
                ->where('memo', $this->transaction->to_memo)
                ->lockForUpdate()
                ->first();
            if (!$wallet) {
                DB::rollBack();
                return;
            }
            $updateFeeAmount = $wallet->amount - $this->transaction->total_fees;
            if ($updateFeeAmount >= 0) {
                DB::table('wallet_ton_memos')->where('id', $wallet->id)
                    ->update(['amount' => $updateFeeAmount, 'updated_at' => Carbon::now()]);
                DB::table('wallet_ton_transactions')->where('id', $this->transaction->id)
                    ->update(['is_sync_total_fees' => true, 'updated_at' => Carbon::now()]);
                printf("Update fee deposit tran id: %s, updateFeeAmount: %s, to memo id: %s \n",
                    $this->transaction->id, $updateFeeAmount, $wallet->id);
            }
            DB::commit();
            return;
        } catch (\Exception $e) {
            DB::rollBack();
            return;
        }
    }
}
