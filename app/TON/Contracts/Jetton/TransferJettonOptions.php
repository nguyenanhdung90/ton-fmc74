<?php declare(strict_types=1);

namespace App\TON\Contracts\Jetton;

use Brick\Math\BigInteger;
use App\TON\Interop\Address;

class TransferJettonOptions
{
    public function __construct(
        BigInteger $jettonAmount,
        Address $toAddress,
        ?Address $responseAddress,
        int $queryId = 0,
        $forwardPayload = null,
        ?BigInteger $forwardAmount = null,
    ) {
    }
}
