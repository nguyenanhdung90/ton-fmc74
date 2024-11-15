<?php

namespace App\TON\Transactions\SyncTransactionToWallet;

use App\TON\TonHelper;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class FetchWithdrawInMsgHashTransaction implements SyncTransactionInterface
{
    protected int $transactionId;

    public function __construct(int $transactionId)
    {
        $this->transactionId = $transactionId;
    }

    public function process(?array $data)
    {
        $transaction = DB::table('wallet_ton_transactions')->where('id', $this->transactionId)->first();
        if (!$transaction) {
            return;
        }
        if (empty($transaction->currency)) {
            return;
        }
        if (!empty($transaction->occur_ton) && !empty($transaction->lt) && !empty($transaction->hash)) {
            return;
        }
        if (empty(Arr::get($data, 'lt'))) {
            return;
        }
        if (empty(Arr::get($data, 'hash'))) {
            return;
        }
        $occurTon = null;
        if (!empty(Arr::get($data, 'out_msgs'))) {
            if ($transaction->currency === TonHelper::TON) {
                $occurTon = Arr::get($data, 'total_fees', 0) + Arr::get($data, 'out_msgs.0.fwd_fee');
            } else {
                $occurTon = Arr::get($data, 'total_fees', 0) + Arr::get($data, 'out_msgs.0.value') +
                    Arr::get($data, 'out_msgs.0.fwd_fee');
            }
        }

        DB::table('wallet_ton_transactions')
            ->where('id', $this->transactionId)
            ->update([
                'lt' => Arr::get($data, 'lt'),
                'hash' => Arr::get($data, 'hash'),
                'occur_ton' => $occurTon,
                'updated_at' => Carbon::now()
            ]);
        printf("Fetch  withdraw tran id: %s, currency: %s \n", $this->transactionId, $transaction->currency);
    }
}
