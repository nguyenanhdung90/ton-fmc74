<?php declare(strict_types=1);

namespace App\TON\Helpers;

use App\TON\Interop\Boc\Builder;
use App\TON\Interop\Boc\Cell;
use App\TON\Interop\Boc\Exceptions\BitStringException;
use App\TON\Interop\Boc\Helpers\TypedArrayHelper;
use App\TON\Interop\Bytes;
use App\TON\TypedArrays\Uint8Array;

final class OffchainHelper
{
    public const ONCHAIN_CONTENT_PREFIX = 0x00;
    public const OFFCHAIN_CONTENT_PREFIX = 0x01;

    /**
     * @throws BitStringException
     */
    public static function createUrlCell(string $uri): Cell
    {
        return (new Builder())
            ->writeUint(self::OFFCHAIN_CONTENT_PREFIX, 8)
            ->writeBytes(Bytes::stringToBytes($uri))
            ->cell();
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function parseUrlCell(Cell $cell): string
    {
        if ($cell->bits->getImmutableArray()[0] !== self::OFFCHAIN_CONTENT_PREFIX) {
            throw new \InvalidArgumentException("Cell contains no offchain content");
        }

        $length = 0;
        $c = $cell;

        while ($c) {
            $length += $c->bits->getImmutableArray()->length;
            $c = $c->refs[0] ?? null;
        }

        $bytes = new Uint8Array($length);
        $length = 0;
        $c = $cell;

        while ($c) {
            $bytes->set($c->bits->getImmutableArray(), $length);
            $length += $c->bits->getImmutableArray()->length;
            $c = $c->refs[0] ?? null;
        }

        return trim(Bytes::arrayToBytes(TypedArrayHelper::sliceUint8Array($bytes, 1)), "\0");
    }
}
