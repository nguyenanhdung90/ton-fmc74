<?php

namespace App\Jobs;

use App\Models\WalletTonTransaction;
use App\TON\Transactions\Excess\CollectAmountAttribute;
use App\TON\Transactions\Excess\CollectExcessTransactionAttribute;
use App\TON\Transactions\Excess\CollectFromAddressWalletAttribute;
use App\TON\Transactions\Excess\CollectHashLtAttribute;
use App\TON\Transactions\Excess\CollectQueryIdAttribute;
use App\TON\Transactions\Excess\CollectToAddressWalletAttribute;
use App\TON\Transactions\Excess\CollectTotalFeesAttribute;
use App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet\TransactionExcess;
use App\TON\Transactions\TransactionHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TonSyncExcessTransaction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            if (empty(Arr::get($this->data, 'in_msg'))) {
                return;
            }

            $collectExcessTransaction = new CollectExcessTransactionAttribute();
            $collectQueryId = new CollectQueryIdAttribute($collectExcessTransaction);
            $collectHashLt = new CollectHashLtAttribute($collectQueryId);
            $collectAmount = new CollectAmountAttribute($collectHashLt);
            $collectFromAddressWallet = new CollectFromAddressWalletAttribute($collectAmount);
            $collectToAddressWallet = new CollectToAddressWalletAttribute($collectFromAddressWallet);
            $collectTotalFees = new CollectTotalFeesAttribute($collectToAddressWallet);
            $trans = $collectTotalFees->collect($this->data);

            if (!$trans['query_id']) {
                return;
            }

            $count = DB::table('wallet_ton_transactions')
                ->where('type', TransactionHelper::WITHDRAW_EXCESS)
                ->where('query_id', $trans['query_id'])
                ->where('currency', $trans['currency'])
                ->count();
            if ($count) {
                return;
            }

            printf("Insert tran hash: %s currency: %s amount: %s \n", $trans['hash'], $trans['currency']
                , $trans['amount']);
            $transactionId = DB::table('wallet_ton_transactions')->insertGetId($trans);
            $excess = new TransactionExcess($transactionId);
            $excess->updateToAmountWallet();
        } catch (\Exception $e) {
            Log::error("Message: " . ' | ' . $e->getMessage());
            printf("Exception: %s \n", $e->getMessage());
        }
    }
}
