<?php declare(strict_types=1);

namespace App\TON\Interop;

use App\TON\Interop\CryptoProviders\DefaultProvider;
use App\TON\TypedArrays\Uint8Array;

/**
 * Cryptographic functions helper
 */
class Crypto
{
    private static ?KeyPairProvider $keyPairProvider = null;

    private static ?DigestProvider $digestProvider = null;

    private static ?SignatureProvider $signatureProvider = null;

    public static final function setKeyPairProvider(KeyPairProvider $keyPairProvider): void
    {
        self::$keyPairProvider = $keyPairProvider;
    }

    public static final function setDigestProvider(DigestProvider $digestProvider): void
    {
        self::$digestProvider = $digestProvider;
    }

    public static final function setSignatureProvider(SignatureProvider $signatureProvider): void
    {
        self::$signatureProvider = $signatureProvider;
    }

    /**
     * Returns SHA-256 hash of given Uint8Array.
     *
     */
    public static final function sha256(Uint8Array $bytes): Uint8Array
    {
        return self::ensureDigestProvider()->digestSha256($bytes);
    }

    /**
     * Returns the public and private keys obtained from the seed.
     *
     */
    public static final function keyPairFromSeed(Uint8Array $seed): KeyPair
    {
        return self::ensureKeyPairProvider()->keyPairFromSeed($seed);
    }

    /**
     * Returns a new random public/private key pair.
     *
     */
    public static final function newKeyPair(): KeyPair
    {
        return self::ensureKeyPairProvider()->newKeyPair();
    }

    /**
     * Returns a new random seed.
     *
     */
    public static final function newSeed(): Uint8Array
    {
        return self::ensureKeyPairProvider()->newSeed();
    }

    /**
     * Calculates cryptographic signature of Uint8Array.
     *
     */
    public static final function sign(Uint8Array $message, Uint8Array $secretKey): Uint8Array
    {
        return self::ensureSignProvider()->signDetached($message, $secretKey);
    }

    private static function getDefProvider(): DefaultProvider
    {
        static $provider;

        if (!$provider) {
            $provider = new DefaultProvider();
        }

        return $provider;
    }

    private static function ensureKeyPairProvider(): KeyPairProvider
    {
        if (!self::$keyPairProvider) {
            self::$keyPairProvider = self::getDefProvider();
        }

        return self::$keyPairProvider;
    }

    private static function ensureDigestProvider(): DigestProvider
    {
        if (!self::$digestProvider) {
            self::$digestProvider = self::getDefProvider();
        }

        return self::$digestProvider;
    }

    private static function ensureSignProvider(): SignatureProvider
    {
        if (!self::$signatureProvider) {
            self::$signatureProvider = self::getDefProvider();
        }

        return self::$signatureProvider;
    }
}
