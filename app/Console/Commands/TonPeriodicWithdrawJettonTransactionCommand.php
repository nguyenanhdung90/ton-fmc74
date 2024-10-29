<?php

namespace App\Console\Commands;

use App\TON\HttpClients\TonCenterClientInterface;
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
                printf("Processing %s withdraw transactions. \n", $withDrawTransactions->count());
                foreach ($withDrawTransactions as $withdrawTx) {
                    sleep(1);
                    $txByMessageList = $tonCenterClient->getTransactionsByMessage(['msg_hash' => $withdrawTx->in_msg_hash]);
                    if (!$txByMessageList) {
                        //printf("Can not get transactions with msg hash: \n", $withdrawTx->in_msg_hash);
                        continue;
                    }

                    $txMsg = $txByMessageList->first();
                    if (!$txMsg) {
                        //printf("Empty transactions \n");
                        continue;
                    }
                    if (!Arr::get($txMsg, 'lt') || !Arr::get($txMsg, 'hash')) {
                        continue;
                    }
                    if (empty(Arr::get($txMsg, 'out_msgs'))) {
                        continue;
                    }

                    DB::transaction(function () use ($withdrawTx, $txMsg) {
                        $totalFees = Arr::get($txMsg, 'total_fees') + Arr::get($txMsg, 'out_msgs.0.fwd_fee')
                            + Arr::get($txMsg, 'out_msgs.0.value');
                        DB::table('wallet_ton_transactions')->where('id', $withdrawTx->id)
                            ->update(['lt' => Arr::get($txMsg, 'lt'),
                                'hash' => Arr::get($txMsg, 'hash'),
                                'total_fees' => $totalFees,
                                'updated_at' => Carbon::now()]);
                        if (!empty($withdrawTx->from_memo)) {
                            $walletTonMemo = DB::table('wallet_ton_memos')
                                ->where('memo', $withdrawTx->from_memo)
                                ->where('currency', TransactionHelper::TON)
                                ->lockForUpdate()->get(['id', 'memo', 'currency', 'amount'])->first();
                            if ($walletTonMemo) {
                                $updateAmount = $walletTonMemo->amount - $totalFees;
                                if ($updateAmount >= 0) {
                                    DB::table('wallet_ton_memos')->where('id', $walletTonMemo->id)
                                        ->update(['amount' => $updateAmount, 'updated_at' => Carbon::now()]);
                                    DB::table('wallet_ton_transactions')->where('id', $withdrawTx->id)
                                        ->update(['is_sync_amount_ton' => true, 'updated_at' => Carbon::now()]);
                                }
                            }

                            $walletJettonMemo = DB::table('wallet_ton_memos')
                                ->where('memo', $withdrawTx->from_memo)
                                ->where('currency', $withdrawTx->currency)
                                ->lockForUpdate()->get(['id', 'memo', 'currency', 'amount'])->first();
                            if ($walletJettonMemo) {
                                $updateJettonAmount = $walletJettonMemo->amount - $withdrawTx->amount;
                                if ($updateJettonAmount >= 0) {
                                    DB::table('wallet_ton_memos')->where('id', $walletJettonMemo->id)
                                        ->update(['amount' => $updateJettonAmount, 'updated_at' => Carbon::now()]);

                                    DB::table('wallet_ton_transactions')->where('id', $walletJettonMemo->id)
                                        ->update(['is_sync_amount_jetton' => true, 'updated_at' => Carbon::now()]);
                                }
                            }
                        }
                    }, 5);
                }
            } catch (\Exception $e) {
                printf("Exception periodic withdraw jetton: " . $e->getMessage());
                continue;
            }
        }
        return Command::SUCCESS;
    }
}
