<?php declare(strict_types=1);

namespace App\TON\Transports\Toncenter\Models;

class JsonRpcResponse
{
    public bool $ok;
    public $result;
    public ?string $error;
    public ?int $code;
    public string $jsonrpc;
    public ?string $id;

    public function __construct(
        bool $ok,
        $result,
        ?string $error,
        ?int $code,
        string $jsonrpc,
        ?string $id
    ) {
        $this->ok = $ok;
        $this->result = $result;
        $this->error = $error;
        $this->code = $code;
        $this->jsonrpc = $jsonrpc;
        $this->id = $id;
    }

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
