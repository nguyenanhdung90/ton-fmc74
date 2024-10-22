<?php declare(strict_types=1);

namespace App\TON\Contracts\Jetton;

use Brick\Math\BigInteger;
use App\TON\Interop\Address;

class BurnOptions
{
    public function __construct(
        BigInteger $jettonAmount,
        ?Address $responseAddress,
        int $queryId = 0,
    ) {}
}
