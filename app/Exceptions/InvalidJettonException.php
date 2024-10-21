<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class InvalidJettonException extends Exception
{
    const INVALID_JETTON = '01';
    const INVALID_JETTON_OPCODE = '02';

    public function render($request): JsonResponse
    {
        $code = (int)$this->getCode();
        $messageCode = $code < 10 ? '0' . $code : $code;
        return response()->json(["error" => true, "message" => $this->getMessage(), 'code' => $messageCode]);
    }
}
