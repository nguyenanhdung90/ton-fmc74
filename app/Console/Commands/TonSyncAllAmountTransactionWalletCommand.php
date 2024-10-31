<?php

namespace App\Console\Commands;

use App\Models\WalletTonTransaction;
use App\TON\Transactions\SyncAmountMemoWallet\SyncDeposit;
use App\TON\Transactions\SyncAmountMemoWallet\SyncMemoWalletAbstract;
use App\TON\Transactions\SyncAmountMemoWallet\SyncTransactionExcess;
use App\TON\Transactions\SyncAmountMemoWallet\SyncWithdrawJetton;
use App\TON\Transactions\SyncAmountMemoWallet\SyncWithdrawTon;
use App\TON\Transactions\TransactionHelper;
use Illuminate\Console\Command;

class TonSyncAllAmountTransactionWalletCommand extends Command
{
    /**
     * php artisan ton:sync_all_amount_transaction_wallet
     *
     * @var string
     */
    protected $signature = 'ton:sync_all_amount_transaction_wallet';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync remaining amount from transaction to memo wallet';

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
    public function handle(): int
    {
        $query = WalletTonTransaction::query();
        $query->where(function ($query) {
            $query->orWhere('is_sync_amount', 0);
            $query->orWhere('is_sync_total_fees', 0);
        })
            ->whereNotNull('hash')
            ->whereNotNull('lt')
            ->limit(TransactionHelper::MAX_LIMIT_TRANSACTION);
        $i = 0;
        while (true) {
            $offset = TransactionHelper::MAX_LIMIT_TRANSACTION * $i;
            $transactions = $query->offset($offset)->get();
            if (!$transactions->count()) {
                printf("Empty transaction \n");
                break;
            }
            $transactions->each(function ($item, $key) {
                /** @var WalletTonTransaction $item */
                if ($item->needSyncExcess()) {
                    $syncMemoWallet = new SyncTransactionExcess($item);
                }
                if ($item->needSyncWithdrawTon()) {
                    $syncMemoWallet = new SyncWithdrawTon($item);
                }
                if ($item->needSyncWithdrawJetton()) {
                    $syncMemoWallet = new SyncWithdrawJetton($item);
                }
                if ($item->needSyncDeposit()) {
                    $syncMemoWallet = new SyncDeposit($item);
                }
                if (!empty($syncMemoWallet) && $syncMemoWallet instanceof SyncMemoWalletAbstract) {
                    $syncMemoWallet->process();
                }
            });
            $i++;
        }
        return Command::SUCCESS;
    }
}
