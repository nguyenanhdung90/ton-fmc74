<?php

namespace App\Console\Commands;

use App\Models\WalletTonTransaction;
use App\TON\Transactions\SyncAmountMemoWallet\SyncFixDepositJetton;
use App\TON\Transactions\SyncAmountMemoWallet\SyncFixDepositTon;
use App\TON\Transactions\SyncAmountMemoWallet\SyncFixExcess;
use App\TON\Transactions\SyncAmountMemoWallet\SyncFixWithdrawJetton;
use App\TON\Transactions\SyncAmountMemoWallet\SyncFixWithdrawTon;
use App\TON\Transactions\SyncAmountMemoWallet\SyncMemoWalletAbstract;
use App\TON\Transactions\TransactionHelper;
use Illuminate\Console\Command;

class TonSyncAllAmountTotalFeesTransactionWalletCommand extends Command
{
    /**
     * php artisan ton:sync_all_fix_amount_fee
     *
     * @var string
     */
    protected $signature = 'ton:sync_all_fix_amount_fee';

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
            //->where('id', 57)
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
                if ($item->type === TransactionHelper::WITHDRAW_EXCESS) {
                    $syncMemoWallet = new SyncFixExcess($item);
                }
                if ($item->type === TransactionHelper::DEPOSIT && $item->currency === TransactionHelper::TON) {
                    $syncMemoWallet = new SyncFixDepositTon($item);
                }
                if ($item->type === TransactionHelper::DEPOSIT && $item->currency !== TransactionHelper::TON) {
                    $syncMemoWallet = new SyncFixDepositJetton($item);
                }
                if ($item->type === TransactionHelper::WITHDRAW && $item->currency === TransactionHelper::TON) {
                    $syncMemoWallet = new SyncFixWithdrawTon($item);
                }
                if ($item->type === TransactionHelper::WITHDRAW && $item->currency !== TransactionHelper::TON) {
                    $syncMemoWallet = new SyncFixWithdrawJetton($item);
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
