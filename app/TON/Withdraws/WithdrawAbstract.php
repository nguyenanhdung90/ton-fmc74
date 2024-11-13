<?php

namespace App\TON\Withdraws;

use App\TON\Exceptions\WithdrawTonException;
use App\TON\TonHelper;
use App\TON\Transactions\SyncTransactionToWallet\TransactionWithdrawRevokeAmount;
use App\TON\Transactions\SyncTransactionToWallet\TransactionWithdrawRevokeFixedFee;
use App\TON\Transports\Toncenter\Models\TonResponse;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

abstract class WithdrawAbstract
{
    abstract public function getWallet($pubicKey);

    /**
     * @throws WithdrawTonException
     */
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
            $walletMemo = DB::table('wallet_memos')->where('memo', $fromMemo)->first();
            if (!$walletMemo) {
                DB::rollBack();
                throw new WithdrawTonException("Memo account does not exist");
            }
            $wallet = DB::table('wallets')
                ->where('user_name', $walletMemo->user_name)
                ->where('currency', $currency)
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();
            if (!$wallet) {
                DB::rollBack();
                throw new WithdrawTonException("Wallet account is disable");
            }
            $remainBalance = $isAllRemainBalance ? 0 : ($wallet->amount - $transferUnit);
            if ($remainBalance < 0) {
                DB::rollBack();
                throw new WithdrawTonException("Amount of wallet is not enough");
            }
            $transfer = $isAllRemainBalance ? $wallet->amount : $transferUnit;
            if ($transfer <= 0) {
                DB::rollBack();
                throw new WithdrawTonException("Amount of transfer must be greater than zero");
            }
            $transaction = [
                'from_address_wallet' => config('services.ton.root_wallet'),
                'from_memo' => $fromMemo,
                'type' => TonHelper::WITHDRAW,
                'to_memo' => $toMemo,
                'to_address_wallet' => $toAddress,
                'currency' => $currency,
                'decimals' => $decimals,
                'amount' => $transfer,
                'is_sync_amount' => true,
                'query_id' => $queryId,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
            $transactionId = DB::table('wallet_ton_transactions')->insertGetId($transaction);
            DB::table('wallets')->where('id', $wallet->id)
                ->update(['amount' => $remainBalance, 'updated_at' => Carbon::now()]);
            DB::commit();
            return $transactionId;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new WithdrawTonException($e->getMessage());
        }
    }

    protected function syncProcessingOrFailedBy(TonResponse $responseMessage, int $transactionId): void
    {
        if (!$responseMessage->ok || empty(Arr::get($responseMessage->result, 'hash'))) {
            $transactionSync = new TransactionWithdrawRevokeAmount($transactionId);
            $transactionSync->syncTransactionWallet();
            $transactionRevoke = new TransactionWithdrawRevokeFixedFee($transactionId);
            $transactionRevoke->syncTransactionWallet();
        } else {
            DB::table('wallet_ton_transactions')->where('id', $transactionId)
                ->update([
                    'status' => TonHelper::PROCESSING,
                    'in_msg_hash' => Arr::get($responseMessage->result, 'hash'),
                    'updated_at' => Carbon::now()
                ]);
        }
    }
}
