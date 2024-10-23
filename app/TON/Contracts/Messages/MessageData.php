<?php declare(strict_types=1);

namespace App\TON\Contracts\Messages;

use App\TON\Interop\Boc\Cell;

class MessageData
{
    public ?Cell $body;
    public ?Cell $state;

    public function __construct(
        ?Cell $body = null,
        ?Cell $state = null
    ) {
        $this->body = $body;
        $this->state = $state;
    }
}
