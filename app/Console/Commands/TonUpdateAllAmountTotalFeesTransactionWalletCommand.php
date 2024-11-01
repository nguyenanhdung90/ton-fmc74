<?php

namespace App\Console\Commands;

use App\Models\WalletTonTransaction;
use App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet\TransactionDepositAmount;
use App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet\TransactionDepositFee;
use App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet\TransactionExcess;
use App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet\TransactionWithdrawAmount;
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
                switch ($item->type) {
                    case TransactionHelper::WITHDRAW_EXCESS:
                        $transaction = new TransactionExcess($item);
                        $transaction->updateToAmountWallet();
                        break;
                    case TransactionHelper::DEPOSIT:
                        if (!$item->is_sync_amount) {
                            $transaction = new TransactionDepositAmount($item);
                            $transaction->updateToAmountWallet();
                        }
                        if (!$item->is_sync_total_fees) {
                            $transaction = new TransactionDepositFee($item);
                            $transaction->updateToAmountWallet();
                        }
                        break;
                    case TransactionHelper::WITHDRAW:
                        if (!$item->is_sync_amount) {
                            $transaction = new TransactionWithdrawAmount($item);
                            $transaction->updateToAmountWallet();
                        }
                        if (!$item->is_sync_total_fees) {
                            $transaction = new TransactionWithdrawFee($item);
                            $transaction->updateToAmountWallet();
                        }
                        break;
                }
            });
            $i++;
        }
        return Command::SUCCESS;
    }
}
