<?php

namespace App\Console\Commands;

use App\Models\WalletTonTransaction;
use App\TON\HttpClients\TonCenterClientInterface;
use App\TON\Interop\Units;
use App\TON\Transactions\TransactionHelper;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class TonPeriodicWithdrawTonTransactionsCommand extends Command
{
    /**
     * php artisan ton:periodic_withdraw_ton
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
                printf("Period transaction withdraw ton query every 5s ...\n");
                sleep(5);
                $withDrawTransactions = DB::table('wallet_ton_transactions')->where('type', config('services.ton.withdraw'))
                    ->whereNull('lt')->limit(TransactionHelper::MAX_LIMIT_TRANSACTION)->get();
                printf("Processing %s withdraw transactions. \n", $withDrawTransactions->count());
                foreach ($withDrawTransactions as $withdrawTx) {
                    sleep(2);
                    $txByMessages = $tonCenterClient->getTransactionsByMessage(['msg_hash' => $withdrawTx->hash]);
                    if (!$txByMessages) {
                        continue;
                    }
                    $txByMessage = $txByMessages->first();
                    if (!$txByMessage) {
                        continue;
                    }

                    $lt = Arr::get($txByMessage, 'lt');
                    $hash = Arr::get($txByMessage, 'hash');
                    if (!$lt || !$hash) {
                        return;
                    }

                    $feeWithDraw = (int)Arr::get($txByMessage, 'total_fees', 0) +
                        (int)Arr::get($txByMessage, 'out_msgs.0.fwd_fee', 0);

                    DB::transaction(function () use ($withdrawTx, $feeWithDraw, $lt, $hash) {
                        DB::table('wallet_ton_transactions')->where('id', $withdrawTx->id)
                            ->update(['lt' => $lt, 'hash' => $hash, 'total_fees' => $feeWithDraw,
                                'updated_at' => Carbon::now()]);
                        if (!empty($withdrawTx->from_memo)) {
                            $walletMemo = DB::table('wallet_ton_memos')->where('memo', $withdrawTx->from_memo)
                                ->where('currency', TransactionHelper::TON)
                                ->lockForUpdate()->get(['id', 'memo', 'currency', 'amount'])->first();
                            if ($walletMemo) {
                                $updateAmount = $walletMemo->amount - ($withdrawTx->amount + $feeWithDraw);
                                if ($updateAmount >= 0) {
                                    DB::table('wallet_ton_memos')->where('id', $walletMemo->id)
                                        ->update(['amount' => $updateAmount, 'updated_at' => Carbon::now()]);
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
