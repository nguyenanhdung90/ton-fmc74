<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Jetton;

use Brick\Math\BigInteger;
use App\TON\Interop\Address;
use App\TON\Interop\Boc\Cell;

class JettonWalletData
{
    public function __construct(
        BigInteger $balance,
        ?Address $ownerAddress,
        ?Address $minterAddress,
        Cell $walletCode,
    ) {
    }
}
