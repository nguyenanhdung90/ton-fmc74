<?php declare(strict_types=1);

namespace App\TON\Contracts\Wallets;

use App\TON\Interop\Address;
use App\TON\Contracts\ContractOptions;
use App\TON\TypedArrays\Uint8Array;

class WalletOptions extends ContractOptions
{
    public Uint8Array $publicKey;
    public int $workchain;
    public ?Address $address;

    public function __construct(
        Uint8Array $publicKey,
        int $workchain = 0,
        ?Address $address = null
    )
    {
        $this->publicKey = $publicKey;
        $this->workchain = $workchain;
        $this->address = $address;
        parent::__construct($workchain, $address);
    }
}
