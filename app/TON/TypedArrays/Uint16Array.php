<?php declare(strict_types=1);

namespace App\TON\TypedArrays;

class Uint16Array extends TypedArray
{
    const BYTES_PER_ELEMENT = 2;
    const ELEMENT_PACK_CODE = 'S';
}
