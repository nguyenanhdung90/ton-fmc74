<?php

namespace App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet;

use App\TON\TonHelper;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UpdateWithdrawSuccessTransaction implements SyncTransactionInterface
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

            if ($transaction->currency === TonHelper::TON) {
                $occurTon = Arr::get($data, 'total_fees', 0) + Arr::get($data, 'out_msgs.0.fwd_fee');
            } else {
                $occurTon = Arr::get($data, 'total_fees', 0) + Arr::get($data, 'out_msgs.0.value') +
                    Arr::get($data, 'out_msgs.0.fwd_fee');
            }
            DB::table('wallet_ton_transactions')
                ->where('id', $this->transactionId)
                ->update([
                    'lt' => Arr::get($data, 'lt'),
                    'hash' => Arr::get($data, 'hash'),
                    'occur_ton' => $occurTon,
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
