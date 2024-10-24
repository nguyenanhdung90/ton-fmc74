<?php declare(strict_types=1);

namespace App\TON\TypedArrays;

class Int8Array extends TypedArray
{
    const BYTES_PER_ELEMENT = 1;
    const ELEMENT_PACK_CODE = 'c';
}
