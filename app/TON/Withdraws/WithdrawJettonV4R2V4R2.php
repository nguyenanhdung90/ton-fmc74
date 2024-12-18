<?php

namespace App\TON\Withdraws;

use App\TON\Contracts\Wallets\V4\WalletV4Options;
use App\TON\Contracts\Wallets\V4\WalletV4R2;

class WithdrawJettonV4R2V4R2 extends WithdrawJettonAbstract implements WithdrawJettonV4R2Interface
{
    public function getWallet($pubicKey): WalletV4R2
    {
        return new WalletV4R2(new WalletV4Options($pubicKey));
    }
}
