<?php

namespace App\Console\Commands;

use App\Models\WalletTonTransaction;
use App\TON\HttpClients\TonCenterClientInterface;
use App\TON\Interop\Units;
use App\TON\Transactions\TransactionHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class TonPeriodicWithdrawTonTransactionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ton:periodic_withdraw_ton';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(TonCenterClientInterface $tonCenterClient)
    {
        while (true) {
            try {
                printf("Period transaction withdraw ton query ...\n");
                sleep(1);
                $withDrawTransactions = DB::table('wallet_ton_transactions')->where('type', config('services.ton.withdraw'))
                    ->whereNull('lt')->limit(TransactionHelper::MAX_LIMIT_TRANSACTION)->get();
                printf("Processing %s withdraw transactions. \n", $withDrawTransactions->count());
                foreach ($withDrawTransactions as $tx) {
                    $transactions = $tonCenterClient->getTransactionsByMessage(['msg_hash' => $tx->hash]);
                    $transaction = $transactions->first();
                    if (!$transaction) {
                        continue;
                    }
                    $feeWithDraw = (int)Arr::get($transaction, 'total_fees', 0) +
                        (int)Arr::get($transaction, 'out_msgs.0.fwd_fee', 0);
                    $lt = Arr::get($transaction, 'lt');
                    $hash = Arr::get($transaction, 'hash');
                    if (!$lt || !$hash) {
                        return;
                    }
                    DB::transaction(function () use ($tx, $feeWithDraw, $lt, $hash) {
                        DB::table('wallet_ton_transactions')->where('id', $tx->id)
                            ->update(['lt' => $lt, 'hash' => $hash, 'total_fees' => $feeWithDraw]);

                        if (!empty($tx->from_memo)) {
                            $walletMemo = DB::table('wallet_ton_memos')->where('memo', $tx->from_memo)
                                ->where('currency', config('services.ton.ton'))
                                ->lockForUpdate()->get(['id', 'memo', 'currency', 'amount'])->first();
                            if ($walletMemo) {
                                $updateAmount = $walletMemo->amount - ($tx->amount + $feeWithDraw);
                                if ($updateAmount >= 0) {
                                    DB::table('wallet_ton_memos')->where('id', $walletMemo->id)
                                        ->update(['amount' => $updateAmount]);
                                }
                            }
                        }
                    }, 5);
                }
            } catch (\Exception $e) {
                printf($e->getMessage());
                continue;
            }
        }
        return 0;
    }
}
