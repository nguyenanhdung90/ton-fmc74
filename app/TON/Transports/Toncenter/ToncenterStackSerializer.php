<?php declare(strict_types=1);

namespace App\TON\Transports\Toncenter;

use App\TON\Interop\Boc\Builder;
use App\TON\Interop\Boc\Exceptions\CellException;
use App\TON\Interop\Boc\Exceptions\SliceException;
use App\TON\Interop\Bytes;
use App\TON\Marshalling\Tvm\Cell;
use App\TON\Marshalling\Tvm\Number;
use App\TON\Marshalling\Tvm\Slice;
use App\TON\Marshalling\Tvm\TvmStackEntry;

final class ToncenterStackSerializer
{

    public static function serialize(array $stack): array
    {
        $result = [];

        foreach ($stack as $idx => $entry) {
            if ($entry instanceof TvmStackEntry) {
                if ($entry instanceof Cell) {
                    $result[] = self::serializeCell($entry);
                    continue;
                }

                if ($entry instanceof Slice) {
                    $result[] = self::serializeSlice($entry);
                    continue;
                }

                if ($entry instanceof Number) {
                    $result[] = self::serializeNumber($entry);
                    continue;
                }

                throw new \RuntimeException("Not implemented serializer for ");
            } else if (is_array($entry) && array_is_list($entry)) {
                $result[] = $entry;
            } else {
                $givenMessage = is_array($entry) ? "associative array" : gettype($entry); // @phpstan-ignore-line

                throw new \InvalidArgumentException(
                    "Incorrect stack entry, list expected, " . $givenMessage . " given; index: " . $idx
                );
            }
        }

        return $result;
    }

    /**
     * @throws CellException
     */
    private static function serializeCell(Cell $entry): array
    {
        return ["cell", Bytes::bytesToBase64($entry->getData()->toBoc( false))];
    }

    /**
     * @throws CellException
     * @throws SliceException
     */
    private static function serializeSlice(Slice $entry): array
    {
        return ["tvm.Slice", Bytes::bytesToBase64(
            (new Builder())->writeSlice($entry->getData())->cell()->toBoc(false),
        )];
    }

    private static function serializeNumber(Number $entry): array
    {
        $n = $entry->getData();

        return ["num", "0x" . (is_int($n) ? dechex($n) : $n->toBase(16))];
    }
}
