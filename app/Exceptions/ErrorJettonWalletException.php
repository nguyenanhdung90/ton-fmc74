<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ErrorJettonWalletException extends Exception
{
    const ERROR_JET_WALLET = '21';

    public function render($request): JsonResponse
    {
        $code = (int)$this->getCode();
        $messageCode = $code < 10 ? '0' . $code : $code;
        return response()->json(["error" => true, "message" => $this->getMessage(), 'code' => $messageCode]);
    }
}
