<?php declare(strict_types=1);

namespace App\TON\TypedArrays;

class Float64Array extends TypedArray
{
    const BYTES_PER_ELEMENT = 8;
    const ELEMENT_PACK_CODE = 'd';
}
