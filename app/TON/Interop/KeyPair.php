<?php declare(strict_types=1);

namespace App\TON\Interop;

use App\TON\Interop\Helpers\OlifantonByteReader;
use App\TON\TypedArrays\Uint8Array;

/**
 * Public/Secret key pair
 */
final class KeyPair
{
    public function __construct(
        public Uint8Array $publicKey,
        public Uint8Array $secretKey,
    ) {}

    public static function fromSecretKey(Uint8Array $secretKey): self
    {
        $publicKey = substr(OlifantonByteReader::getBytes($secretKey->buffer), 32);

        return new KeyPair(
            Bytes::bytesToArray($publicKey),
            $secretKey,
        );
    }
}
