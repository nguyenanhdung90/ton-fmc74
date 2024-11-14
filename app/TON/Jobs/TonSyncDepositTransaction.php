<?php

namespace App\TON\Jobs;

use App\TON\Transactions\Deposit\CollectHashLtCurrencyAttribute;
use App\TON\Transactions\Deposit\CollectMemoSenderAmountAttribute;
use App\TON\Transactions\Deposit\CollectOccurTonAttribute;
use App\TON\Transactions\Deposit\CollectTransactionAttribute;
use App\TON\Transactions\SyncTransactionToWallet\TransactionDepositAmount;
use App\TON\Transactions\SyncTransactionToWallet\TransactionDepositOccur;
use App\TON\TonHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TonSyncDepositTransaction implements ShouldQueue
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
            if (count(Arr::get($this->data, 'out_msgs'))) {
                // This is not received transaction
                return;
            }
            $source = Arr::get($this->data, 'in_msg.source');
            if (empty($source)) {
                // This is failed message
                return;
            }
            if ($this->mapperSource->has($source)) {
                $sourceDetails = $this->mapperSource->get($source);
                Arr::set($this->data, 'in_msg.source_details', $sourceDetails);
                if (Arr::get($sourceDetails, 'jetton_master.currency') === TonHelper::NONSUPPORT_CURRENCY) {
                    // This is non support jetton
                    return;
                }
            } else {
                Arr::set($this->data, 'in_msg.source_details', null);
            }

            $hash = Arr::get($this->data, 'transaction_id.hash');
            $existedTransaction = DB::table('wallet_ton_transactions')
                ->where('hash', $hash)->where('type', TonHelper::DEPOSIT)->count();
            if ($existedTransaction) {
                return;
            }

            $collectTransaction = new CollectTransactionAttribute();
            $hashLtCurrency = new CollectHashLtCurrencyAttribute($collectTransaction);
            $occurTon = new CollectOccurTonAttribute($hashLtCurrency);
            $memoSenderAmount = new CollectMemoSenderAmountAttribute($occurTon);
            $transaction = $memoSenderAmount->collect($this->data);

            $transactionId = DB::table('wallet_ton_transactions')->insertGetId($transaction);
            printf("Insert tran id: %s currency: %s amount: %s \n", $transactionId, $transaction['currency']
                , $transaction['amount']);
            $depositAmount = new TransactionDepositAmount($transactionId);
            $depositAmount->syncTransactionWallet();
            $depositFee = new TransactionDepositOccur($transactionId);
            $depositFee->syncTransactionWallet();
        } catch (\Exception $e) {
            Log::error("Exception message: " . ' | ' . $e->getMessage());
            printf("Exception: %s \n", $e->getMessage());
        }
    }
}
