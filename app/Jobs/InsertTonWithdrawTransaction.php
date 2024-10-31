<?php

namespace App\Jobs;

use App\TON\Transactions\TransactionHelper;
use App\TON\Transports\Toncenter\Models\TonResponse;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InsertTonWithdrawTransaction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private TonResponse $tonResponse;

    private string $fromMemo;
    private string $toAddress;
    private float $transferUnit;
    private string $currency;
    private int $decimals;
    private string $toMemo;
    private ?int $queryId;
    private bool $isAllRemainBalance;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        TonResponse $tonResponse,
        string $fromMemo,
        string $toAddress,
        int $transferUnit,
        string $currency,
        int $decimals,
        string $toMemo,
        ?int $queryId = null,
        bool $isAllRemainBalance = false
    ) {
        $this->tonResponse = $tonResponse;
        $this->fromMemo = $fromMemo;
        $this->toAddress = $toAddress;
        $this->transferUnit = $transferUnit;
        $this->currency = $currency;
        $this->decimals = $decimals;
        $this->toMemo = $toMemo;
        $this->queryId = $queryId;
        $this->isAllRemainBalance = $isAllRemainBalance;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            if (!$this->tonResponse->ok) {
                // false withdraw
                return;
            }
            $result = $this->tonResponse->result;
            $msgHash = Arr::get($result, 'hash');
            if (empty($msgHash)) {
                // There is no hash message
                return;
            }
            $transaction = [
                'from_address_wallet' => config('services.ton.root_ton_wallet'),
                'from_memo' => $this->fromMemo,
                'type' => TransactionHelper::WITHDRAW,
                'to_memo' => $this->toMemo,
                'to_address_wallet' => $this->toAddress,
                'in_msg_hash' => $msgHash,
                'currency' => $this->currency,
                'decimals' => $this->decimals,
                'query_id' => $this->queryId,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
            if (!$this->isAllRemainBalance) {
                $transaction['amount'] = $this->transferUnit;
            }
            DB::table('wallet_ton_transactions')->insert($transaction);
        } catch (\Exception $e) {
            Log::error('InsertTonWithdrawTransaction: ' . $e->getMessage());
            if (!empty($transaction)) {
                Log::error('Error insert transactions withdraw: ', $transaction);
            }
            printf("Exception Insert Withdraw Ton Transaction : %s \n", $e->getMessage());
        }
    }
}
