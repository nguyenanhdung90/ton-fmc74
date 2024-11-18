<?php

namespace App\Console\Commands;

use App\TON\HttpClients\TonCenterClientInterface;
use App\TON\TonHelper;
use App\TON\Transactions\SyncTransactionToWallet\TransactionWithdrawRevokeAmount;
use App\TON\Transactions\SyncTransactionToWallet\TransactionWithdrawRevokeFixedFee;
use App\TON\Transactions\SyncTransactionToWallet\TransactionWithdrawSuccess;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class TonPeriodicWithdrawJettonTransferTransactionCommand extends Command
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
    protected $description = '';

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
        $params = [
            "owner_address" => config("services.ton.root_wallet"),
            "limit" => TonHelper::MAX_LIMIT_TRANSACTION,
            "direction" => "out",
            "sort" => "asc",
        ];
        $counterEmptyJetton = 0;
        while (true) {
            try {
                printf("Period transaction withdraw ton query every 20s ...\n");
                sleep(20);
                $jettonTransfer = DB::table('jetton_transfers')->orderBy("lt", "DESC")->first();
                if ($jettonTransfer) {
                    $params["start_lt"] = $jettonTransfer->lt;
                }
                $jettons = $tonCenterClient->getJettonTransfers($params);
                if (!$jettons) {
                    printf("Get error jetton transfer. \n");
                    continue;
                }

                $this->syncStatusWithdrawTransaction($jettonTransfer, $counterEmptyJetton);
                if ($this->isGetEmptyJettonTransfer($jettonTransfer, $jettons)) {
                    $counterEmptyJetton++;
                    continue;
                }
                $counterEmptyJetton = 0;

                $jettons->transform(function ($item, $key) {
                    return [
                        'query_id' => Arr::get($item, 'query_id'),
                        'trace_id' => Arr::get($item, 'trace_id'),
                        'lt' => Arr::get($item, 'transaction_lt'),
                        'jetton_master' => strtolower($item['jetton_master']),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                });
                DB::table('jetton_transfers')->insertOrIgnore($jettons->toArray());
                printf("Insert %s jetton transfer \n", $jettons->count());
            } catch (\Exception $e) {
                printf("Exception periodic withdraw jetton: " . $e->getMessage());
                continue;
            }
        }
        return Command::SUCCESS;
    }

    private function isGetEmptyJettonTransfer($jettonTransfer, $jettons): bool
    {
        if (empty($jettonTransfer)) {
            printf("Get empty jetton transfer  in the fist loop. \n");
            return $jettons->count() == 0;
        } else {
            printf("Get empty jetton transfer. \n");
            return $jettons->count() == 1;
        }
    }

    private function syncStatusWithdrawTransaction($jettonTransfer, int $counterEmptyJetton)
    {
        $transactionQuery = DB::table('wallet_ton_transactions')
            ->whereNotNull('lt')
            ->whereNotNull('hash')
            ->whereNotNull('query_id')
            ->where("type", "=", TonHelper::WITHDRAW)
            ->where("currency", "!=", TonHelper::TON)
            ->whereNotIn("status", [TonHelper::FAILED, TonHelper::SUCCESS])
            ->limit(TonHelper::MAX_LIMIT_QUERY);
        if ($counterEmptyJetton < TonHelper::MAX_COUNTER_EMPTY_JETTON_SET_STATUS) {
            if (!$jettonTransfer || empty($jettonTransfer->lt)) {
                return;
            }
            $transactionQuery->where("lt", "<=", $jettonTransfer->lt);
        } else {
            printf("Three times get new empty jetton transfer. Sync all transaction withdraw status \n");
        }
        $transactions = $transactionQuery->get();
        foreach ($transactions as $item) {
            $existedTransfer = DB::table('jetton_transfers')
                ->where("trace_id", $item->hash)
                ->where("query_id", $item->query_id)
                ->first();
            if ($existedTransfer) {
                printf("Success jetton withdraw id: %s \n", $item->id);
                $withdrawSuccess = new TransactionWithdrawSuccess($item->id);
                $withdrawSuccess->syncTransactionWallet();
            } else {
                printf("Failed jetton withdraw id:: %s \n", $item->id);
                $withdrawRevoke = new TransactionWithdrawRevokeAmount($item->id);
                $withdrawRevoke->syncTransactionWallet();
                $withdrawRevokeFixedFee = new TransactionWithdrawRevokeFixedFee($item->id);
                $withdrawRevokeFixedFee->syncTransactionWallet();
            }
        }
    }
}
