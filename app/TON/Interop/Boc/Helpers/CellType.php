<?php declare(strict_types=1);

namespace App\TON\Interop\Boc\Helpers;

//enum CellType : int
//{
//    case ORDINARY = -1;
//    case PRUNED_BRANCH = 1;
//    case LIBRARY = 2;
//    case MERKLE_PROOF = 3;
//    case MERKLE_UPDATE = 4;
//}

class CellType
{
    const ORDINARY = 'ORDINARY';
    const PRUNED_BRANCH = 'PRUNED_BRANCH';
    const LIBRARY = 'LIBRARY';
    const MERKLE_PROOF = 'MERKLE_PROOF';
    const MERKLE_UPDATE = 'MERKLE_UPDATE';
    const CELL_TYPE = [
        -1 => 'ORDINARY',
        1 => 'PRUNED_BRANCH',
        2 => 'LIBRARY',
        3 => 'MERKLE_PROOF',
        4 => 'MERKLE_UPDATE'
    ];

    public static function tryFrom(int $id): ?string
    {
        return self::CELL_TYPE[$id] ?? null;
    }
}
