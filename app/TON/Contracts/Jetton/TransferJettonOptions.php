<?php declare(strict_types=1);

namespace App\TON\Contracts\Jetton;

use Brick\Math\BigInteger;
use App\TON\Interop\Address;

class TransferJettonOptions
{
    public BigInteger $jettonAmount;
    public Address $toAddress;
    public ?Address $responseAddress;
    public int $queryId;
    public $forwardPayload;
    public ?BigInteger $forwardAmount;

    public function __construct(
        BigInteger $jettonAmount,
        Address $toAddress,
        ?Address $responseAddress,
        int $queryId = 0,
        $forwardPayload = null,
        ?BigInteger $forwardAmount = null
    ) {
        $this->jettonAmount = $jettonAmount;
        $this->toAddress = $toAddress;
        $this->responseAddress = $responseAddress;
        $this->queryId = $queryId;
        $this->forwardPayload = $forwardPayload;
        $this->forwardAmount = $forwardAmount;
    }
}
