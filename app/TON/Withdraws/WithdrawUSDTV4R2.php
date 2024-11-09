<?php

namespace App\TON\Withdraws;

use App\TON\Contracts\Wallets\V4\WalletV4Options;
use App\TON\Contracts\Wallets\V4\WalletV4R2;
use App\TON\Interop\Units;
use App\TON\TonHelper;

class WithdrawUSDTV4R2 extends WithdrawJettonAbstract implements WithdrawUSDTV4R2Interface
{
    public function getWallet($pubicKey): WalletV4R2
    {
        return new WalletV4R2(new WalletV4Options($pubicKey));
    }

    public function getCurrency(): string
    {
        return TonHelper::USDT;
    }

    public function getDecimals(): int
    {
        return Units::USDt;
    }

    public function getMasterJettonAddress(): string
    {
        return config('services.ton.master_jetton_usdt');
    }
}
