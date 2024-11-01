<?php

namespace App\Console\Commands;

use App\Models\WalletTonTransaction;
use App\TON\HttpClients\TonCenterClientInterface;
use App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet\TransactionWithdrawAmount;
use App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet\TransactionWithdrawFee;
use App\TON\Transactions\TransactionHelper;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class TonPeriodicWithdrawJettonTransactionCommand extends Command
{
    /**
     * php artisan ton:periodic_withdraw_jetton
     *
     * @var string
     */
    protected $signature = 'ton:periodic_withdraw_jetton';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'periodic sync withdraw transaction only for Jetton';

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
     * @param TonCenterClientInterface $tonCenterClient
     * @return int
     */
    public function handle(TonCenterClientInterface $tonCenterClient): int
    {
        while (true) {
            try {
                printf("Period transaction withdraw jetton query every 20 ...\n");
                sleep(20);
                $withDrawTransactions = DB::table('wallet_ton_transactions')
                    ->where('type', TransactionHelper::WITHDRAW)
                    ->where('currency', '!=', TransactionHelper::TON)
                    ->whereNotNull('in_msg_hash')
                    ->where('created_at', '<=', Carbon::now()->subSeconds(30)->format('Y-m-d H:i:s'))
                    ->whereNull('lt')
                    ->whereNotNull('in_msg_hash')
                    ->limit(TransactionHelper::MAX_LIMIT_TRANSACTION)
                    ->get();
                if (!$withDrawTransactions->count()) {
                    continue;
                }

                printf("Check over %s transactions \n", $withDrawTransactions->count());
                foreach ($withDrawTransactions as $withdrawTx) {
                    sleep(1);
                    $txByMessages = $tonCenterClient->getTransactionsByMessage(['msg_hash' => $withdrawTx->in_msg_hash]);
                    if (!$txByMessages) {
                        continue;
                    }

                    $txByMessage = $txByMessages->first();
                    if (!$txByMessage) {
                        continue;
                    }
                    if (!Arr::get($txByMessage, 'lt') || !Arr::get($txByMessage, 'hash')) {
                        continue;
                    }
                    if (empty(Arr::get($txByMessage, 'out_msgs'))) {
                        continue;
                    }

                    $totalFees = Arr::get($txByMessage, 'total_fees') + Arr::get($txByMessage, 'out_msgs.0.fwd_fee')
                        + Arr::get($txByMessage, 'out_msgs.0.value');
                    DB::table('wallet_ton_transactions')
                        ->where('id', $withdrawTx->id)
                        ->update([
                            'lt' => Arr::get($txByMessage, 'lt'),
                            'hash' => Arr::get($txByMessage, 'hash'),
                            'total_fees' => $totalFees,
                            'updated_at' => Carbon::now()
                        ]);
                    $withdrawAmount = new TransactionWithdrawAmount($withdrawTx->id);
                    $withdrawAmount->updateToAmountWallet();
                    $withdrawFee = new TransactionWithdrawFee($withdrawTx->id);
                    $withdrawFee->updateToAmountWallet();
                }
            } catch (\Exception $e) {
                printf("Exception periodic withdraw jetton: " . $e->getMessage());
                continue;
            }
        }
        return Command::SUCCESS;
    }
}
