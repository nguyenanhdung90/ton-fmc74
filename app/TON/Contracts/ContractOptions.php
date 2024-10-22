<?php declare(strict_types=1);

namespace App\TON\Contracts;

use Olifanton\Interop\Address;

class ContractOptions
{
    public function __construct(
        int $workchain = 0,
        ?Address $address = null
    ) {
    }
}
