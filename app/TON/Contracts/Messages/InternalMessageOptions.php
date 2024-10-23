<?php declare(strict_types=1);

namespace App\TON\Contracts\Messages;

use Brick\Math\BigInteger;
use App\TON\Interop\Address;

class InternalMessageOptions
{
    public bool $bounce;
    public Address $dest;
    public BigInteger $value;
    public ?Address $src;
    public ?BigInteger $ihrFee;
    public ?BigInteger $fwdFee;
    public ?bool $ihrDisabled;
    public ?bool $bounced;
    public ?string $createdLt;
    public ?string $createdAt;

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
    ) {
        $this->bounce = $bounce;
        $this->dest = $dest;
        $this->value = $value;
        $this->src = $src;
        $this->ihrFee = $ihrFee;
        $this->fwdFee = $fwdFee;
        $this->ihrDisabled = $ihrDisabled;
        $this->bounced = $bounced;
        $this->createdLt = $createdLt;
        $this->createdAt = $createdAt;
    }
}
