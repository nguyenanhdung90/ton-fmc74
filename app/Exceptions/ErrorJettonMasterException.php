<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ErrorJettonMasterException extends Exception
{
    const ERROR_JET_MASTER = '11';

    public function render($request): JsonResponse
    {
        $code = (int)$this->getCode();
        $messageCode = $code < 10 ? '0' . $code : $code;
        return response()->json(["error" => true, "message" => $this->getMessage(), 'code' => $messageCode]);
    }
}
