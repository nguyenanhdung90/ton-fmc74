<?php

namespace App\TON\Withdraws;

use App\Models\WalletTonMemo;
use App\TON\Exceptions\WithdrawTonException;
use App\TON\HttpClients\TonCenterClientInterface;
use App\TON\Transactions\SyncAmountFeeTransactionToMemoWallet\TransactionFailedWithdrawAmount;
use App\TON\Transactions\TransactionHelper;
use App\TON\Transports\Toncenter\ClientOptions;
use App\TON\Transports\Toncenter\Models\TonResponse;
use App\TON\Transports\Toncenter\ToncenterHttpV2Client;
use App\TON\Transports\Toncenter\ToncenterTransport;
use Carbon\Carbon;
use Http\Client\Common\HttpMethodsClient;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

abstract class WithdrawAbstract
{
    abstract public function getWallet($pubicKey);

    protected function getBaseUri(): string
    {
        return config('services.ton.is_main') ? TonCenterClientInterface::MAIN_BASE_URI
            : TonCenterClientInterface::TEST_BASE_URI;
    }

    protected function getTonApiKey()
    {
        return config('services.ton.is_main') ? config('services.ton.api_key_main') :
            config('services.ton.api_key_test');
    }

    protected function getTransport(): ToncenterTransport
    {
        $httpClient = new HttpMethodsClient(
            Psr18ClientDiscovery::find(),
            Psr17FactoryDiscovery::findRequestFactory(),
            Psr17FactoryDiscovery::findStreamFactory(),
        );
        $tonCenter = new ToncenterHttpV2Client(
            $httpClient,
            new ClientOptions(
                $this->getBaseUri() . "api/v2",
                $this->getTonApiKey()
            )
        );
        return new ToncenterTransport($tonCenter);
    }

    /**
     * @throws WithdrawTonException
     */
    protected function validGetWalletMemo(string $fromMemo, string $currency)
    {
        $wallet = WalletTonMemo::where('memo', $fromMemo)->where('currency', $currency)->first();
        if (!$wallet) {
            throw new WithdrawTonException("There is not memo account");
        }
        return $wallet;
    }

    protected function syncToWalletGetIdTransaction(
        string $fromMemo,
        string $toAddress,
        int $transferUnit,
        string $currency,
        int $decimals,
        string $toMemo,
        ?int $queryId = null,
        bool $isAllRemainBalance = false): ?int
    {
        DB::beginTransaction();
        try {
            $wallet = DB::table('wallet_ton_memos')
                ->where('currency', $currency)
                ->where('memo', $fromMemo)
                ->lockForUpdate()
                ->first();
            if (!$wallet) {
                DB::rollBack();
                return null;
            }
            $fixedFee = TransactionHelper::getFixedFeeByCurrency($currency);
            $remainFixedFeeBalance = $wallet->amount - $fixedFee;
            if ($remainFixedFeeBalance < 0) {
                DB::rollBack();
                throw new WithdrawTonException("Minimum amount of wallet is greater than " . $fixedFee);
            }
            $remainBalance = $isAllRemainBalance ? $remainFixedFeeBalance : ($remainFixedFeeBalance - $transferUnit);
            if ($remainBalance < 0) {
                DB::rollBack();
                throw new WithdrawTonException("Amount of wallet is not enough");
            }
            $amount = $isAllRemainBalance ? $remainFixedFeeBalance : $transferUnit;
            $transaction = [
                'from_address_wallet' => config('services.ton.root_ton_wallet'),
                'from_memo' => $fromMemo,
                'type' => TransactionHelper::WITHDRAW,
                'to_memo' => $toMemo,
                'to_address_wallet' => $toAddress,
                'currency' => $currency,
                'decimals' => $decimals,
                'amount' => $amount,
                'is_sync_amount' => true,
                'query_id' => $queryId,
                'fixed_fee' => config('services.ton.fixed_fee_ton'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
            $transactionId = DB::table('wallet_ton_transactions')->insertGetId($transaction);
            DB::table('wallet_ton_memos')->where('id', $wallet->id)
                ->update(['amount' => $remainBalance, 'updated_at' => Carbon::now()]);
            DB::commit();
            return $transactionId;
        } catch (\Exception $e) {
            DB::rollBack();
            return null;
        }
    }

    protected function syncBy(TonResponse $responseMessage, int $transactionId): void
    {
        if (!$responseMessage->ok || empty(Arr::get($responseMessage->result, 'hash'))) {
            $withdrawAmount = new TransactionFailedWithdrawAmount($transactionId);
            $withdrawAmount->syncTransactionWallet();
        } else {
            DB::table('wallet_ton_transactions')->where('id', $transactionId)
                ->update([
                    'status' => TransactionHelper::PROCESSING,
                    'in_msg_hash' => Arr::get($responseMessage->result, 'hash'),
                    'updated_at' => Carbon::now()
                ]);
        }
    }
}
