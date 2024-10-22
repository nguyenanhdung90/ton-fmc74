<?php declare(strict_types=1);

namespace App\TON\Transports\Toncenter\Models;

class TonResponse
{
    public function __construct(
        bool   $ok,
        mixed  $result,
        ?string $error,
        ?int    $code
    ) {}
}
