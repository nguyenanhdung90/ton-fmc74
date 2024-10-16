<?php declare(strict_types=1);

namespace App\TON\Interop;

use App\TON\Interop\Exceptions\CryptoException;
use App\TON\TypedArrays\Uint8Array;

interface KeyPairProvider
{
    /**
     * @throws CryptoException
     */
    public function keyPairFromSeed(Uint8Array $seed): KeyPair;

    /**
     * @throws CryptoException
     */
    public function newKeyPair(): KeyPair;

    /**
     * @throws CryptoException
     */
    public function newSeed(): Uint8Array;
}
