<?php declare(strict_types=1);

namespace App\TON\TypedArrays;

class Float32Array extends TypedArray
{
    const BYTES_PER_ELEMENT = 4;
    const ELEMENT_PACK_CODE = 'f';
}
