<?php

namespace App\Jobs;

use App\TON\Transactions\Deposit\CollectHashLtCurrencyAttribute;
use App\TON\Transactions\Deposit\CollectDecimalsAttribute;
use App\TON\Transactions\Deposit\CollectMemoSenderAmountAttribute;
use App\TON\Transactions\Deposit\CollectTotalFeesAttribute;
use App\TON\Transactions\Deposit\CollectTransactionAttribute;
use App\TON\Transactions\SyncAmountMemoWallet\SyncDeposit;
use App\TON\Transactions\SyncAmountMemoWallet\SyncMemoWalletAbstract;
use App\TON\Transactions\TransactionHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncTonDepositTransaction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $data;

    private Collection $mapperSource;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $data, Collection $mapperSource)
    {
        $this->data = $data;
        $this->mapperSource = $mapperSource;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $source = Arr::get($this->data, 'in_msg.source');
            if (!empty($source) && $this->mapperSource->has($source)) {
                Arr::set($this->data, 'in_msg.source_details', $this->mapperSource->get($source));
            } else {
                Arr::set($this->data, 'in_msg.source_details', null);
            }
            if (count(Arr::get($this->data, 'out_msgs'))) {
                // This is not received transaction
                return;
            }
            $hash = Arr::get($this->data, 'transaction_id.hash');
            $countTransaction = DB::table('wallet_ton_transactions')
                ->where('hash', $hash)
                ->where('type', TransactionHelper::DEPOSIT)
                ->count();
            if ($countTransaction) {
                return;
            }

            $collectTransaction = new CollectTransactionAttribute();
            $hashLtCurrency = new CollectHashLtCurrencyAttribute($collectTransaction);
            $totalFee = new CollectTotalFeesAttribute($hashLtCurrency);
            $memoSenderAmount = new CollectMemoSenderAmountAttribute($totalFee);
            $decimals = new CollectDecimalsAttribute($memoSenderAmount);
            $trans = $decimals->collect($this->data);

            printf("Inserting tran hash: %s currency: %s amount: %s \n", $trans['hash'], $trans['currency'],
                $trans['amount']);

            $transactionId = DB::table('wallet_ton_transactions')->insertGetId($trans);
            $transaction = DB::table('wallet_ton_transactions')->find($transactionId);
            if ($transaction) {
                $syncMemoWallet = new SyncDeposit($transaction);
                $syncMemoWallet->process();
            }
        } catch (\Exception $e) {
            Log::error("Exception message: " . ' | ' . $e->getMessage());
            //printf("Exception: %s \n", $e->getMessage());
        }
    }
}
