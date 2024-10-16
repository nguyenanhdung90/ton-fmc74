<?php declare(strict_types=1);

namespace App\TON\Interop;

use App\TON\TypedArrays\Uint8Array;
use App\TON\Interop\Exceptions\CryptoException;

interface SignatureProvider
{
    /**
     * @throws CryptoException
     */
    public function signDetached(Uint8Array $message, Uint8Array $secretKey): Uint8Array;
}
