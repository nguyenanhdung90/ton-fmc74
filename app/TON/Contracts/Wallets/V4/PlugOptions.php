<?php declare(strict_types=1);

namespace App\TON\Contracts\Wallets\V4;

use Brick\Math\BigInteger;
use App\TON\Interop\Address;

class PlugOptions
{
    public function __construct(
        int $seqno,
        Address $pluginAddress,
        BigInteger $amount,
        ?int $queryId = null,
        ?int $expireAt = null
    ) {}
}
