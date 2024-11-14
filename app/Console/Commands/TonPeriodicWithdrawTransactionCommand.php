<?php

namespace App\Console\Commands;

use App\TON\HttpClients\TonCenterClientInterface;
use App\TON\Transactions\SyncTransactionToWallet\TransactionWithdrawRevokeAmount;
use App\TON\Transactions\SyncTransactionToWallet\TransactionWithdrawRevokeFixedFee;
use App\TON\Transactions\SyncTransactionToWallet\TransactionWithdrawSuccess;
use App\TON\TonHelper;
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
                    ->limit(TonHelper::MAX_LIMIT_TRANSACTION)
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
                    if ($txByMessages->count() !== 1) {
                        printf("Failed with empty transaction, id: %s \n", $withdrawTransaction->id);
                        $withdrawAmount = new TransactionWithdrawRevokeAmount($withdrawTransaction->id);
                        $withdrawAmount->syncTransactionWallet();
                        continue;
                    }
                    $txByMessage = $txByMessages->first();

                    if ($this->isSuccessWithdrawTransaction($txByMessage)) {
                        $withdrawSuccess = new TransactionWithdrawSuccess($withdrawTransaction->id);
                        $withdrawSuccess->syncTransactionWallet($txByMessage);
                    } else {
                        printf("Failed with empty out_msgs, id: %s \n", $withdrawTransaction->id);
                        $withdrawRevoke = new TransactionWithdrawRevokeAmount($withdrawTransaction->id);
                        $withdrawRevoke->syncTransactionWallet($txByMessage);
                        $withdrawRevokeFixedFee = new TransactionWithdrawRevokeFixedFee($withdrawTransaction->id);
                        $withdrawRevokeFixedFee->syncTransactionWallet($txByMessage);
                    }
                }
            } catch (\Exception $e) {
                printf("Exception periodic withdraw ton: " . $e->getMessage());
                continue;
            }
        }
        return Command::SUCCESS;
    }

    public function isSuccessWithdrawTransaction(array $txByMessage): bool
    {
        try {
            $outMsgs = Arr::get($txByMessage, 'out_msgs');
            if (empty($outMsgs)) {
                return false;
            }
            $body = Arr::get($txByMessage, 'out_msgs.0.message_content.body');
            if (empty($body)) {
                return false;
            }
            TonHelper::parseJetBody($body);
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
