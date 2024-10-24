<?php declare(strict_types=1);

namespace App\TON\Contracts\Jetton;

use Brick\Math\BigInteger;
use App\TON\Interop\Address;

class BurnOptions
{
    public BigInteger $jettonAmount;
    public ?Address $responseAddress;
    public int $queryId;

    public function __construct(
        BigInteger $jettonAmount,
        ?Address $responseAddress,
        int $queryId = 0
    ) {
        $this->jettonAmount = $jettonAmount;
        $this->responseAddress = $responseAddress;
        $this->queryId = $queryId;
    }
}
