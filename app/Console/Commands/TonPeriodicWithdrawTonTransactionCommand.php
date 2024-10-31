<?php

namespace App\Console\Commands;

use App\Models\WalletTonTransaction;
use App\TON\HttpClients\TonCenterClientInterface;
use App\TON\Transactions\SyncAmountMemoWallet\SyncWithdrawTon;
use App\TON\Transactions\TransactionHelper;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class TonPeriodicWithdrawTonTransactionCommand extends Command
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
                    ->where('currency', TransactionHelper::TON)
                    ->where('created_at', '<=', Carbon::now()->subSeconds(30)->format('Y-m-d H:i:s'))
                    ->whereNull('lt')->whereNotNull('in_msg_hash')
                    ->limit(TransactionHelper::MAX_LIMIT_TRANSACTION)->get();
                if (!$withDrawTransactions->count()) {
                    continue;
                }
               // printf("Processing %s withdraw transactions. \n", $withDrawTransactions->count());
                foreach ($withDrawTransactions as $withdrawTx) {
                    sleep(1);
                    $txByMessages = $tonCenterClient->getTransactionsByMessage(['msg_hash' => $withdrawTx->in_msg_hash]);
                    if (!$txByMessages) {
                        printf("Can not get transactions with msg hash: \n", $withdrawTx->in_msg_hash);
                        continue;
                    }
                    $txByMessage = $txByMessages->first();
                    if (!$txByMessage) {
                        continue;
                    }
                    if (empty(Arr::get($txByMessage, 'out_msgs'))) {
                        continue;
                    }
                    if (!Arr::get($txByMessage, 'lt') || !Arr::get($txByMessage, 'hash')) {
                        continue;
                    }

                    $updatedTransaction = $this->getUpdatedTransactionBy($withdrawTx, $txByMessage);
                    DB::table('wallet_ton_transactions')
                        ->where('id', $withdrawTx->id)
                        ->update($updatedTransaction);
                    $transaction = WalletTonTransaction::find($withdrawTx->id);
                    $syncMemoWallet = new SyncWithdrawTon($transaction);
                    $syncMemoWallet->process();
                }
            } catch (\Exception $e) {
                printf("Exception periodic withdraw ton: " . $e->getMessage());
                continue;
            }
        }
        return Command::SUCCESS;
    }

    private function isWithdrawAllRemainingBalance($withdrawTx): bool
    {
        return is_null($withdrawTx->amount);
    }

    private function getUpdatedTransactionBy($withdrawTx, $txByMessage): array
    {
        $feeWithDraw = (int)Arr::get($txByMessage, 'total_fees', 0) +
            (int)Arr::get($txByMessage, 'out_msgs.0.fwd_fee', 0);
        $updatedTransaction = [
            'lt' => Arr::get($txByMessage, 'lt'),
            'hash' => Arr::get($txByMessage, 'hash'),
            'total_fees' => $feeWithDraw,
            'updated_at' => Carbon::now()
        ];
        if ($this->isWithdrawAllRemainingBalance($withdrawTx)) {
            $updatedTransaction['amount'] = (int)Arr::get($txByMessage, 'out_msgs.0.value', 0);
        }
        return $updatedTransaction;
    }
}
