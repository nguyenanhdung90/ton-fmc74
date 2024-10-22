<?php declare(strict_types=1);

namespace App\TON\Contracts\Messages;

use Brick\Math\BigInteger;
use App\TON\Interop\Address;

class ExternalMessageOptions
{
    public function __construct(
        ?Address $src = null,
        ?Address $dest = null,
        ?BigInteger $importFee = null,
    ) {}
}
