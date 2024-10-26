<?php

namespace App\Jobs;

use App\TON\Interop\Units;
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

class InsertTonWithdrawTransaction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private TonResponse $tonResponse;

    private string $fromMemo;
    private string $toAddress;
    private float $transferAmount;
    private string $currency;
    private int $decimals;
    private string $toMemo;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        TonResponse $tonResponse,
        string $fromMemo,
        string $toAddress,
        float $transferAmount,
        string $currency,
        int $decimals,
        string $toMemo
    ) {
        $this->tonResponse = $tonResponse;
        $this->fromMemo = $fromMemo;
        $this->toAddress = $toAddress;
        $this->transferAmount = $transferAmount;
        $this->currency = $currency;
        $this->decimals = $decimals;
        $this->toMemo = $toMemo;
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
            $tonTransaction = [
                'from_address_wallet' => config('services.ton.root_ton_wallet'),
                'from_memo' => $this->fromMemo,
                'type' => TransactionHelper::WITHDRAW,
                'to_memo' => $this->toMemo,
                'to_address_wallet' => $this->toAddress,
                'in_msg_hash' => $msgHash,
                'amount' => (string)Units::toNano($this->transferAmount),
                'currency' => $this->currency,
                'decimals' => $this->decimals,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
            DB::table('wallet_ton_transactions')->insert($tonTransaction);
        } catch (\Exception $e) {
            printf("Exception Insert Withdraw Ton Transaction : %s \n", $e->getMessage());
        }
    }
}