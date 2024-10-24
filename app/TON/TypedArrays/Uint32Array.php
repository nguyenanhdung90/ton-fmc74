<?php declare(strict_types=1);

namespace App\TON\TypedArrays;

class Uint32Array extends TypedArray
{
    const BYTES_PER_ELEMENT = 4;
    const ELEMENT_PACK_CODE = 'L';
}
