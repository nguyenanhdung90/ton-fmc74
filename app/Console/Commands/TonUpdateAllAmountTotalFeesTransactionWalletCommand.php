<?php

namespace App\Console\Commands;

use App\Models\WalletTonTransaction;
use App\TON\Transactions\SyncAmountMemoWallet\UpdateTransactionWithdrawFee;
use App\TON\Transactions\SyncAmountMemoWallet\UpdateTransactionWithdrawAmount;
use App\TON\Transactions\SyncAmountMemoWallet\SyncMemoWalletAbstract;
use App\TON\Transactions\SyncAmountMemoWallet\UpdateTransactionDepositAmount;
use App\TON\Transactions\SyncAmountMemoWallet\UpdateTransactionDepositFee;
use App\TON\Transactions\SyncAmountMemoWallet\UpdateTransactionExcess;
use App\TON\Transactions\TransactionHelper;
use Illuminate\Console\Command;

class TonUpdateAllAmountTotalFeesTransactionWalletCommand extends Command
{
    /**
     * php artisan ton:update_all_amount_fee_transaction
     *
     * @var string
     */
    protected $signature = 'ton:update_all_amount_fee_transaction';

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
            printf("Check over %s transactions \n", $transactions->count());
            $transactions->each(function ($item, $key) {
                if ($item->type === TransactionHelper::WITHDRAW_EXCESS) {
                    $syncMemoWallet = new UpdateTransactionExcess($item);
                }

                if ($item->type === TransactionHelper::DEPOSIT && !$item->is_sync_amount) {
                    $syncMemoWallet = new UpdateTransactionDepositAmount($item);
                }
                if ($item->type === TransactionHelper::DEPOSIT && !$item->is_sync_total_fees) {
                    $syncMemoWallet = new UpdateTransactionDepositFee($item);
                }


                if ($item->type === TransactionHelper::WITHDRAW && !$item->is_sync_amount) {
                    $syncMemoWallet = new UpdateTransactionWithdrawAmount($item);
                }
                if ($item->type === TransactionHelper::WITHDRAW && !$item->is_sync_total_fees) {
                    $syncMemoWallet = new UpdateTransactionWithdrawFee($item);
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
