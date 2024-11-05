<?php

namespace App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet;

use App\TON\Transactions\TransactionHelper;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UpdateSuccessWithdrawAmountTransaction implements UpdateAmountFeeTransactionInterface
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
            if (!$transaction || empty($transaction->from_memo) || empty($transaction->currency)) {
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

            if ($transaction->currency === TransactionHelper::TON) {
                $totalFees = Arr::get($data, 'total_fees_of_ton', 0);
            } else {
                $totalFees = Arr::get($data, 'total_fees_of_ton', 0) + Arr::get($data, 'out_msgs.0.value');
            }
            DB::table('wallet_ton_transactions')
                ->where('id', $transaction->id)
                ->update([
                    'lt' => Arr::get($data, 'lt'),
                    'hash' => Arr::get($data, 'hash'),
                    'total_fees_of_ton' => $totalFees,
                    'status' => TransactionHelper::SUCCESS,
                    'updated_at' => Carbon::now()
                ]);
            DB::commit();
            return;
        } catch (\Exception $e) {
            DB::rollBack();
            return;
        }
    }

    private function isSameSourceDestinationOfWithdraw(array $transactionByMessage): bool
    {
        if (empty(Arr::get($transactionByMessage, 'out_msgs'))) {
            return false;
        }
        if (empty(Arr::get($transactionByMessage, 'out_msgs.0.source'))) {
            return false;
        }
        if (empty(Arr::get($transactionByMessage, 'out_msgs.0.destination'))) {
            return false;
        }
        return Arr::get($transactionByMessage, 'out_msgs.0.destination')
            === Arr::get($transactionByMessage, 'out_msgs.0.source');
    }
}
