<?php declare(strict_types=1);

namespace App\TON\Transports\Toncenter\Models;

class JsonRpcResponse
{
    public function __construct(
        bool   $ok,
        mixed  $result,
        ?string $error,
        ?int    $code,
        string $jsonrpc,
        ?string $id,
    ) {}

    public function asTonResponse(): TonResponse
    {
        return new TonResponse(
            $this->ok,
            $this->result,
            $this->error,
            $this->code,
        );
    }
}
