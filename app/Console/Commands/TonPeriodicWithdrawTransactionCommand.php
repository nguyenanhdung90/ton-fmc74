<?php

namespace App\Console\Commands;

use App\TON\HttpClients\TonCenterClientInterface;
use App\TON\TonHelper;
use App\TON\Transactions\SyncTransactionToWallet\TransactionWithdrawFetchInMsgHash;
use App\TON\Transactions\SyncTransactionToWallet\TransactionWithdrawRevokeAmount;
use App\TON\Transactions\SyncTransactionToWallet\TransactionWithdrawRevokeFixedFee;
use App\TON\Transactions\SyncTransactionToWallet\TransactionWithdrawSuccess;
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
                    ->where('type', TonHelper::WITHDRAW)
                    ->where('created_at', '<=', Carbon::now()->subSeconds(30)->format('Y-m-d H:i:s'))
                    ->whereNotIn('status', [TonHelper::SUCCESS, TonHelper::FAILED])
                    ->whereNotNull('in_msg_hash')
                    ->limit(TonHelper::MAX_LIMIT_QUERY)
                    ->get();
                if (!$withDrawTransactions->count()) {
                    continue;
                }
                printf("Check over %s transactions \n", $withDrawTransactions->count());
                foreach ($withDrawTransactions as $withdrawTransaction) {
                    sleep(1);
                    $txByMessages = $tonCenterClient->getTransactionsByMessage([
                        'msg_hash' => $withdrawTransaction->in_msg_hash,
                        'direction' => 'in'
                    ]);
                    if (!$txByMessages) {
                        continue;
                    }
                    $txByMessage = $txByMessages->first();
                    if (!$txByMessage) {
                        printf("Get empty transaction by message hash.\n");
                        continue;
                    }
                    if ($withdrawTransaction->currency === TonHelper::TON) {
                        if ($this->isSuccessTonWithdraw($txByMessage)) {
                            $withdrawSuccess = new TransactionWithdrawSuccess($withdrawTransaction->id);
                            $withdrawSuccess->syncTransactionWallet($txByMessage);
                        } else {
                            printf("Failed with empty out_msgs, id: %s \n", $withdrawTransaction->id);
                            $withdrawRevoke = new TransactionWithdrawRevokeAmount($withdrawTransaction->id);
                            $withdrawRevoke->syncTransactionWallet($txByMessage);
                            $withdrawRevokeFixedFee = new TransactionWithdrawRevokeFixedFee($withdrawTransaction->id);
                            $withdrawRevokeFixedFee->syncTransactionWallet();
                        }
                    }
                    $withdrawFetchInMsgHash = new TransactionWithdrawFetchInMsgHash($withdrawTransaction->id);
                    $withdrawFetchInMsgHash->syncTransactionWallet($txByMessage);
                }
            } catch (\Exception $e) {
                printf("Exception periodic withdraw ton: " . $e->getMessage());
                continue;
            }
        }
        return Command::SUCCESS;
    }

    public function isSuccessTonWithdraw(array $txByMessage): bool
    {
        return !empty(Arr::get($txByMessage, 'out_msgs'));
    }
}
