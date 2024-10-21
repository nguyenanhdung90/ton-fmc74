<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class InvalidWithdrawMemoToMemoException extends Exception
{
    const NONE_EXIST_SOURCE_MEMO = '31';
    const AMOUNT_SOURCE_MEMO_NOT_ENOUGH = '32';
    const NONE_EXIST_DESTINATION_MEMO = '33';
    const INVALID_AMOUNT = '34';

    public function render($request): JsonResponse
    {
        $code = (int)$this->getCode();
        $messageCode = $code < 10 ? '0' . $code : $code;
        return response()->json(["error" => true, "message" => $this->getMessage(), 'code' => $messageCode]);
    }
}
