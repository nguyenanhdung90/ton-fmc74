<?php

namespace App\TON\Withdraws;

use App\TON\Contracts\Wallets\V4\WalletV4Options;
use App\TON\Contracts\Wallets\V4\WalletV4R2;
use App\TON\Interop\Units;
use App\TON\Transactions\TransactionHelper;

class WithdrawNOTV4R2 extends WithdrawJettonAbstract implements WithdrawNOTV4R2Interface
{
    public function getWallet($pubicKey): WalletV4R2
    {
        return new WalletV4R2(new WalletV4Options($pubicKey));
    }

    public function getCurrency(): string
    {
        return TransactionHelper::NOT;
    }

    public function getDecimals(): int
    {
        return Units::NOT;
    }

    public function getMasterJettonAddress(): string
    {
        return config('services.ton.root_not');
    }
}
