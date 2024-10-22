<?php declare(strict_types=1);

namespace App\TON\Helpers;

use App\TON\Interop\Bytes;
use App\TON\Interop\Crypto;
use App\TON\TypedArrays\Uint8Array;

final class KeyPair
{
    /**
     * @return \App\TON\Interop\KeyPair
     * @throws \App\TON\Interop\Exceptions\CryptoException
     */
    public static function random(): \App\TON\Interop\KeyPair
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpComposerExtensionStubsInspection */
        return Crypto::keyPairFromSeed(
            new Uint8Array(Bytes::bytesToArray(random_bytes(SODIUM_CRYPTO_SIGN_SEEDBYTES))),
        );
    }
}
