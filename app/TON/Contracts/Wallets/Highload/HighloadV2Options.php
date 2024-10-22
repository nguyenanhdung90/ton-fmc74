<?php declare(strict_types=1);

namespace App\TON\Contracts\Wallets\Highload;

use App\TON\Interop\Address;
use App\TON\Contracts\Wallets\WalletOptions;
use App\TON\TypedArrays\Uint8Array;

class HighloadV2Options extends WalletOptions
{
    public function __construct(
        Uint8Array $publicKey,
        int $subwalletId = 0,
        int $workchain = 0,
        ?Address $address = null
    )
    {
        parent::__construct($publicKey, $workchain, $address);
    }
}
