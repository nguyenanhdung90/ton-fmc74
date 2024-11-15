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
        while (true) {
            try {
                printf("Period transaction withdraw ton query every 20s ...\n");
                sleep(20);
                $jettonTransfer = DB::table('jetton_transfers')->orderBy("lt", "DESC")->first();
                if ($jettonTransfer) {
                    $params["start_lt"] = $jettonTransfer->lt;
                    $this->syncStatusWithdrawTransaction($jettonTransfer->lt);
                }

                $jettons = $tonCenterClient->getJettonTransfers($params);
                if (!$jettons) {
                    printf("Get error jetton transfer. \n");
                    continue;
                }
                if (!empty($jettonTransfer) && $jettons->count() == 1) {
                    printf("Get empty jetton transfer. \n");
                    continue;
                }
                if (empty($jettonTransfer) && $jettons->count() == 0) {
                    printf("Get empty jetton transfer  in the fist loop. \n");
                    continue;
                }
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

    private function syncStatusWithdrawTransaction(int $transferLt)
    {
        $transactions = DB::table('wallet_ton_transactions')
            ->whereNotNull('lt')
            ->whereNotNull('hash')
            ->whereNotNull('query_id')
            ->where("lt", "<=", $transferLt)
            ->where("type", "=", TonHelper::WITHDRAW)
            ->where("currency", "!=", TonHelper::TON)
            ->whereNotIn("status", [TonHelper::FAILED, TonHelper::SUCCESS])
            ->limit(5000)
            ->get();
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
