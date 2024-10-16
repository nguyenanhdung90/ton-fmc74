<?php declare(strict_types=1);

namespace App\TON\Interop;

use App\TON\Interop\Exceptions\CryptoException;
use App\TON\TypedArrays\Uint8Array;

interface DigestProvider
{
    /**
     * @throws CryptoException
     */
    public function digestSha256(Uint8Array $bytes): Uint8Array;
}
