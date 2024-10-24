<?php declare(strict_types=1);

namespace App\TON\Contracts\Jetton;

use App\TON\Interop\Address;
use App\TON\Interop\Boc\Cell;
use App\TON\Contracts\ContractOptions;

class JettonWalletOptions extends ContractOptions
{
    public ?Cell $code;
    public int $workchain;
    public ?Address $address;

    public function __construct(
        ?Cell $code = null,
        int $workchain = 0,
        ?Address $address = null
    ) {
        $this->code = $code;
        $this->workchain = $workchain;
        $this->address = $address;
        parent::__construct($workchain, $address);
    }
}
