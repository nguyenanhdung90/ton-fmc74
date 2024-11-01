<?php

namespace App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet;

use App\Models\WalletTonTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UpdateDepositAmountTransaction implements UpdateAmountFeeTransactionInterface
{
    protected WalletTonTransaction $transaction;

    public function __construct(WalletTonTransaction $transaction)
    {
        $this->transaction = $transaction;
    }

    public function process()
    {
        if (empty($this->transaction->to_memo) || $this->transaction->is_sync_amount
            || empty($this->transaction->currency)) {
            return;
        }
        DB::beginTransaction();
        try {
            $wallet = DB::table('wallet_ton_memos')
                ->where('currency', $this->transaction->currency)
                ->where('memo', $this->transaction->to_memo)
                ->lockForUpdate()
                ->first();
            if (!$wallet) {
                DB::rollBack();
                return;
            }
            $updateAmount = $wallet->amount + $this->transaction->amount;
            DB::table('wallet_ton_memos')->where('id', $wallet->id)
                ->update(['amount' => $updateAmount, 'updated_at' => Carbon::now()]);
            DB::table('wallet_ton_transactions')->where('id', $this->transaction->id)
                ->update(['is_sync_amount' => true, 'updated_at' => Carbon::now()]);
            printf("Update amount deposit tran id: %s, update amount: %s, currency: %s, to memo id: %s \n",
                $this->transaction->id, $updateAmount, $this->transaction->currency, $wallet->id);
            DB::commit();
            return;
        } catch (\Exception $e) {
            DB::rollBack();
            return;
        }
    }
}
