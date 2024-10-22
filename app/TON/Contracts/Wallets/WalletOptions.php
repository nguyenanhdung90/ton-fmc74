<?php declare(strict_types=1);

namespace App\TON\Contracts\Wallets;

use App\TON\Interop\Address;
use App\TON\Contracts\ContractOptions;
use App\TON\TypedArrays\Uint8Array;

class WalletOptions extends ContractOptions
{
    public function __construct(
        Uint8Array $publicKey,
        int $workchain = 0,
        ?Address $address = null
    )
    {
        parent::__construct($workchain, $address);
    }
}
