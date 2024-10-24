<?php declare(strict_types=1);

namespace App\TON\Contracts\Wallets;

use App\TON\Network;

class WalletId
{
    public Network $networkId;
    public int $subwalletId;
    public string $walletVersion;
    public ?int $workchain;

    public function __construct(
        int $networkId = Network::MAIN,
        int $subwalletId = 0,
        string $walletVersion = "v5",
        ?int $workchain = null
    ) {
        $this->networkId = $networkId;
        $this->subwalletId = $subwalletId;
        $this->walletVersion = $walletVersion;
        $this->workchain = $workchain;
    }
}
