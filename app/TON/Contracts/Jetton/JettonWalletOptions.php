<?php declare(strict_types=1);

namespace App\TON\Contracts\Jetton;

use App\TON\Interop\Address;
use App\TON\Interop\Boc\Cell;
use App\TON\Contracts\ContractOptions;

class JettonWalletOptions extends ContractOptions
{
    public function __construct(
        ?Cell $code = null,
        int $workchain = 0,
        ?Address $address = null
    )
    {
        parent::__construct($workchain, $address);
    }
}
