<?php

namespace App\Jobs;

use App\Tons\Transactions\TransactionHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InsertDepositTonTransaction implements ShouldQueue
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
        Log::info($this->data);
        if (count(Arr::get($this->data, 'out_msgs'))) {
            // This is not received transaction
            return;
        }
        $hash = Arr::get($this->data, 'hash');
        $countTransaction = DB::table('wallet_ton_transactions')->where('hash', $hash)->count();
        if ($countTransaction) {
            return;
        }
    }
}
