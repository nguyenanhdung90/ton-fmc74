<?php

namespace App\TON\Withdraws;

use App\Exceptions\InvalidWithdrawMemoToMemoException;
use App\Models\WalletTonMemo;
use App\TON\Transactions\TransactionHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WithdrawMemoToMemo implements WithdrawMemoToMemoInterface
{
    /**
     * @throws InvalidWithdrawMemoToMemoException
     */
    public function transfer(string $fromMemo, string $toMemo, int $amount, string $currency)
    {
        $sourceWalletTonMemo = WalletTonMemo::where('memo', $fromMemo)
            ->where('currency', $currency)->lockForUpdate()
            ->first();
        if (!$sourceWalletTonMemo) {
            throw new InvalidWithdrawMemoToMemoException('None exist source memo',
                InvalidWithdrawMemoToMemoException::NONE_EXIST_SOURCE_MEMO);
        }
        $decimals = $sourceWalletTonMemo->decimals;
        $amountUnit = $amount * pow(10, $decimals);
        if ($amountUnit <= 0) {
            throw new InvalidWithdrawMemoToMemoException('Amount is not less than zero',
                InvalidWithdrawMemoToMemoException::INVALID_AMOUNT);
        }
        $updateSourceAmount = $sourceWalletTonMemo->amount - $amountUnit;
        $destinationWalletTonMemo = WalletTonMemo::where('memo', $toMemo)
            ->where('currency', $currency)->lockForUpdate()
            ->first();
        if (!$destinationWalletTonMemo) {
            throw new InvalidWithdrawMemoToMemoException('None exist destination memo',
                InvalidWithdrawMemoToMemoException::NONE_EXIST_DESTINATION_MEMO);
        }
        if ($amountUnit > $sourceWalletTonMemo->amount) {
            throw new InvalidWithdrawMemoToMemoException('Amount is not enough',
                InvalidWithdrawMemoToMemoException::AMOUNT_SOURCE_MEMO_NOT_ENOUGH);
        }
        $updateDestinationAmount = $destinationWalletTonMemo->amount + $amountUnit;
        DB::transaction(function () use (
            $fromMemo, $toMemo, $amountUnit, $currency, $updateSourceAmount,
            $updateDestinationAmount, $decimals
        ) {
            DB::table('wallet_ton_memos')->where('memo', $fromMemo)->where('currency', $currency)
                ->update(['amount' => $updateSourceAmount]);
            DB::table('wallet_ton_memos')->where('memo', $toMemo)->where('currency', $currency)
                ->update(['amount' => $updateDestinationAmount]);
            DB::table('wallet_ton_transactions')->insert([
                'from_address_wallet' => null,
                'from_memo' => $fromMemo,
                'type' => config('services.ton.deposit'),
                'to_memo' => $toMemo,
                'amount' => $amountUnit,
                'decimals' => $decimals,
                'hash' => TransactionHelper::uniqueTransactionHash(),
                'currency' => $currency,
                'total_fees' => 0,
                'lt' => Carbon::now()->timestamp,
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now(),
            ]);
        }, 5);
    }
}
