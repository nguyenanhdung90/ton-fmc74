<?php declare(strict_types=1);

namespace App\TON\Contracts\Wallets\V4;

use Brick\Math\BigInteger;
use App\TON\Interop\Address;

class PlugOptions
{
    public int $seqno;
    public Address $pluginAddress;
    public BigInteger $amount;
    public ?int $queryId;
    public ?int $expireAt;

    public function __construct(
        int $seqno,
        Address $pluginAddress,
        BigInteger $amount,
        ?int $queryId = null,
        ?int $expireAt = null
    ) {
        $this->seqno = $seqno;
        $this->pluginAddress = $pluginAddress;
        $this->amount = $amount;
        $this->queryId = $queryId;
        $this->expireAt = $expireAt;
    }
}
