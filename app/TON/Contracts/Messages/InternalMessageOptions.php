<?php declare(strict_types=1);

namespace App\TON\Contracts\Messages;

use Brick\Math\BigInteger;
use App\TON\Interop\Address;

class InternalMessageOptions
{
    public function __construct(
        bool $bounce,
        Address $dest,
        BigInteger $value,
        ?Address $src = null,
        ?BigInteger $ihrFee = null,
        ?BigInteger $fwdFee = null,
        ?bool $ihrDisabled = null,
        ?bool $bounced = null,
        ?string $createdLt = null,
        ?string $createdAt = null
    ) {}
}
