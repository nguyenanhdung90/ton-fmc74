<?php

namespace App\Console\Commands;

use App\TON\HttpClients\TonCenterClientInterface;
use App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet\TransactionRevokeWithdrawAmount;
use App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet\TransactionSuccessWithdrawAmount;
use App\TON\Transactions\TransactionHelper;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class TonPeriodicWithdrawTransactionCommand extends Command
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
    protected $description = 'periodic sync withdraw transaction and wallet memo only for Ton';

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
     * @param TonCenterClientInterface $tonCenterClient
     * @return int
     */
    public function handle(TonCenterClientInterface $tonCenterClient): int
    {
        while (true) {
            try {
                printf("Period transaction withdraw ton query every 20s ...\n");
                sleep(20);
                $withDrawTransactions = DB::table('wallet_ton_transactions')
                    ->where('type', TransactionHelper::WITHDRAW)
                    ->where('created_at', '<=', Carbon::now()->subSeconds(30)->format('Y-m-d H:i:s'))
                    ->whereNotIn('status', [TransactionHelper::SUCCESS, TransactionHelper::FAILED])
                    ->whereNotNull('in_msg_hash')
                    ->limit(TransactionHelper::MAX_LIMIT_TRANSACTION)
                    ->get();
                if (!$withDrawTransactions->count()) {
                    continue;
                }
                printf("Check over %s transactions \n", $withDrawTransactions->count());
                foreach ($withDrawTransactions as $withdrawTransaction) {
                    sleep(1);
                    $txByMessages = $tonCenterClient->getTransactionsByMessage(['msg_hash' => $withdrawTransaction->in_msg_hash]);
                    if (!$txByMessages) {
                        continue;
                    }
                    $txByMessage = $txByMessages->first();
                    if (!$txByMessage) {
                        printf("Failed with empty transaction, id: %s \n", $withdrawTransaction->id);
                        $withdrawAmount = new TransactionRevokeWithdrawAmount($withdrawTransaction->id);
                        $withdrawAmount->syncTransactionWallet();
                        continue;
                    }
                    if (empty(Arr::get($txByMessage, 'out_msgs'))) {
                        printf("Failed with empty out_msgs, id: %s \n", $withdrawTransaction->id);
                        $withdrawAmount = new TransactionRevokeWithdrawAmount($withdrawTransaction->id);
                    } else {
                        $withdrawAmount = new TransactionSuccessWithdrawAmount($withdrawTransaction->id);
                    }
                    $withdrawAmount->syncTransactionWallet($txByMessage);
                }
            } catch (\Exception $e) {
                printf("Exception periodic withdraw ton: " . $e->getMessage());
                continue;
            }
        }
        return Command::SUCCESS;
    }
}
