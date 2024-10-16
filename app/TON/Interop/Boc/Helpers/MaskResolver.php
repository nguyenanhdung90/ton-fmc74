<?php declare(strict_types=1);

namespace App\TON\Interop\Boc\Helpers;

use App\TON\Interop\Boc\BitString;
use App\TON\Interop\Boc\Cell;
use App\TON\Interop\Boc\Slice;
use ArrayObject;

final class MaskResolver
{
    /**
     * @param ArrayObject<Cell> $refs
     */
    public static function get(BitString $bits, string $type, ArrayObject $refs): LevelMask
    {
        if ($type === CellType::ORDINARY) {
            $mask = 0;

            foreach ($refs as $ref) {
                /** @var Cell $ref */
                $mask = $mask | $ref->getLevelMask()->getValue();
            }

            return new LevelMask($mask);
        }

        if ($type === CellType::PRUNED_BRANCH) {
            $reader = new Slice(
                $bits->getImmutableArray(),
                $bits->getLength(),
                [],
            );
            $reader->skipBits(8); // type

            if ($bits->getLength() === 280) {
                return new LevelMask(1);
            }

            return new LevelMask($reader->loadUint(8)->toInt());
        }

        if ($type === CellType::LIBRARY) {
            return new LevelMask(0);
        }

        if ($type === CellType::MERKLE_PROOF) {
            return new LevelMask($refs[0]->getLevelMask()->getValue() >> 1);
        }

        if ($type === CellType::MERKLE_UPDATE) {
            /** @var Cell $r0 */
            $r0 = $refs[0];
            /** @var Cell $r1 */
            $r1 = $refs[1];

            return new LevelMask(
                $r0->getLevelMask()->getValue() | $r1->getLevelMask()->getValue() >> 1,
            );
        }

        throw new \RuntimeException("Unsupported cell type: " . $type); // @phpstan-ignore-line
    }
}
