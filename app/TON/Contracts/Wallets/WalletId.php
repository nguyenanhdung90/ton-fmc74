<?php declare(strict_types=1);

namespace App\TON\Contracts\Wallets;

use App\TON\Network;

class WalletId
{
    public function __construct(
        int $networkId = Network::MAIN,
        int $subwalletId = 0,
        string $walletVersion = "v5",
        ?int $workchain = null
    ) {}
}
