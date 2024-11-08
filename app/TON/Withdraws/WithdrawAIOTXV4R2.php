<?php

namespace App\TON\Withdraws;

use App\TON\Contracts\Wallets\V4\WalletV4Options;
use App\TON\Contracts\Wallets\V4\WalletV4R2;
use App\TON\Interop\Units;
use App\TON\TonHelper;

class WithdrawAIOTXV4R2 extends WithdrawJettonAbstract implements WithdrawAIOTXV4R2Interface
{
    public function getWallet($pubicKey): WalletV4R2
    {
        return new WalletV4R2(new WalletV4Options($pubicKey));
    }

    public function getCurrency(): string
    {
        return TonHelper::AIOTX;
    }

    public function getDecimals(): int
    {
        return Units::AIOTX;
    }

    public function getMasterJettonAddress(): string
    {
        // this coin is only test environment
        return '0:226e80c4bffa91adc11dad87706d52cd397047c128456ed2866d0549d8e2b163';
    }
}
