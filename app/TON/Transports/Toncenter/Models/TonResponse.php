<?php declare(strict_types=1);

namespace App\TON\Transports\Toncenter\Models;

class TonResponse
{
    public bool $ok;
    public $result;
    public ?string $error;
    public ?int $code;

    public function __construct(
        bool $ok,
        $result,
        ?string $error,
        ?int $code
    ) {
        $this->ok = $ok;
        $this->result = $result;
        $this->error = $error;
        $this->code = $code;
    }
}
