<?php declare(strict_types=1);

namespace App\TON\Contracts\Messages;

use App\TON\Interop\Address;

class ExternalOutMessageOptions
{
    public function __construct(
        Address $dest,
        ?Address $src = null,
        ?string $createdLt = null,
        ?string $createdAt = null
    ) {}
}
