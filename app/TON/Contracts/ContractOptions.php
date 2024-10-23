<?php declare(strict_types=1);

namespace App\TON\Contracts;

use App\TON\Interop\Address;

class ContractOptions
{
    public int $workchain;

    public ?Address $address;

    public function __construct(
        int $workchain = 0,
        ?Address $address = null
    ) {
        $this->workchain = $workchain;
        $this->address = $address;
    }
}
