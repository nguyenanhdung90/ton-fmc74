<?php declare(strict_types=1);

namespace App\TON\Interop\Boc\Helpers;

use App\TON\Interop\Boc\BitString;
use App\TON\Interop\Boc\Slice;

final class CellTypeResolver
{
    /**
     */
    public static function get(BitString $bytes): string
    {
        $reader = new Slice(
            $bytes->getImmutableArray(),
            $bytes->getLength(),
            [],
        );

        $typeId = $reader->preloadUint(8)->toInt();
        $type = CellType::tryFrom($typeId);

        if (!$type) {
            throw new \InvalidArgumentException("Unknown exotic cell type with id: " . $typeId);
        }

        return $type;
    }
}
