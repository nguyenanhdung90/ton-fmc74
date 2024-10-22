<?php declare(strict_types=1);

namespace App\TON\Contracts\Wallets\V4;

use App\TON\Interop\Address;
use App\TON\Contracts\Wallets\WalletOptions;
use App\TON\TypedArrays\Uint8Array;

class WalletV4Options extends WalletOptions
{
    public int $walletId;
    public function __construct(
        Uint8Array $publicKey,
        int $walletId = 698983191,
        int $workchain = 0,
        ?Address $address = null
    )
    {
        parent::__construct($publicKey, $workchain, $address);
    }
}
