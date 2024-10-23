<?php declare(strict_types=1);

namespace App\TON\Contracts\Messages;

use App\TON\Interop\Address;

class ExternalOutMessageOptions
{
    public Address $dest;
    public ?Address $src;
    public ?string $createdLt;
    public ?string $createdAt;

    public function __construct(
        Address $dest,
        ?Address $src = null,
        ?string $createdLt = null,
        ?string $createdAt = null
    ) {
        $this->dest = $dest;
        $this->src = $src;
        $this->createdLt = $createdLt;
        $this->createdAt = $createdAt;
    }
}
