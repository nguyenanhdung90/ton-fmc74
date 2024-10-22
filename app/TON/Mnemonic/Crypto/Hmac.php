<?php declare(strict_types=1);

namespace App\TON\Mnemonic\Crypto;

use App\TON\TypedArrays\Uint8Array;
use App\TON\Mnemonic\Exceptions\TonMnemonicException;
use App\TON\Interop\Bytes;

final class Hmac
{
    /**
     * @throws TonMnemonicException
     */
    public static function hmacSha512(string $phrase, string $password): Uint8Array
    {
        try {
            $result = hash_hmac(
                'sha512',
                $password,
                $phrase,
                true,
            );

            return Bytes::bytesToArray($result);
        } catch (\Throwable $e) {
            throw new TonMnemonicException("hash_hmac error: " . $e->getMessage(), $e->getCode(), $e);
        }
    }
}
