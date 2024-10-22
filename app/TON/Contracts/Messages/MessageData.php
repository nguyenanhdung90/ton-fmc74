<?php declare(strict_types=1);

namespace App\TON\Contracts\Messages;

use App\TON\Interop\Boc\Cell;

class MessageData
{
    public function __construct(
        ?Cell $body = null,
        ?Cell $state = null
    ) {}
}
