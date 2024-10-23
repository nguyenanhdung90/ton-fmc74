<?php declare(strict_types=1);

namespace App\TON\Contracts\Jetton;

use Brick\Math\BigInteger;
use App\TON\Interop\Address;

class MintOptions
{
    public BigInteger $jettonAmount;
    public Address $destination;
    public BigInteger $amount;
    public int $queryId;

    public function __construct(
        BigInteger $jettonAmount,
        Address $destination,
        BigInteger $amount,
        int $queryId = 0
    ) {
        $this->jettonAmount = $jettonAmount;
        $this->destination = $destination;
        $this->amount = $amount;
        $this->queryId = $queryId;
    }
}
