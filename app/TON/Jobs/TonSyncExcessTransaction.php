<?php

namespace App\TON\Jobs;

use App\TON\Transactions\Excess\CollectAmountAttribute;
use App\TON\Transactions\Excess\CollectExcessTransactionAttribute;
use App\TON\Transactions\Excess\CollectFromAddressWalletAttribute;
use App\TON\Transactions\Excess\CollectHashLtAttribute;
use App\TON\Transactions\Excess\CollectQueryIdAttribute;
use App\TON\Transactions\Excess\CollectToAddressWalletAttribute;
use App\TON\Transactions\Excess\CollectOccurTonAttribute;
use App\TON\TonHelper;
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

            $excessTransaction = new CollectExcessTransactionAttribute();
            $queryId = new CollectQueryIdAttribute($excessTransaction);
            $hashLt = new CollectHashLtAttribute($queryId);
            $amount = new CollectAmountAttribute($hashLt);
            $collectFromAddressWallet = new CollectFromAddressWalletAttribute($amount);
            $toAddressWallet = new CollectToAddressWalletAttribute($collectFromAddressWallet);
            $occurTon = new CollectOccurTonAttribute($toAddressWallet);
            $trans = $occurTon->collect($this->data);

            if (!$trans['query_id']) {
                return;
            }

            $existedExcess = DB::table('wallets_ton_transactions')
                ->where('type', TonHelper::WITHDRAW_EXCESS)
                ->where('query_id', $trans['query_id'])
                ->where('currency', $trans['currency'])
                ->count();
            if ($existedExcess) {
                return;
            }

            printf("Insert tran hash: %s currency: %s amount: %s \n", $trans['hash'], $trans['currency']
                , $trans['amount']);
            DB::table('wallets_ton_transactions')->insert($trans);
        } catch (\Exception $e) {
            Log::error("Message: " . ' | ' . $e->getMessage());
            printf("Exception: %s \n", $e->getMessage());
        }
    }
}
