<?php declare(strict_types=1);

namespace App\TON\TypedArrays;

class Uint8Array extends TypedArray
{
    const BYTES_PER_ELEMENT = 1;
    const ELEMENT_PACK_CODE = 'C';

    public function fGet(int $offset)
    {
        $bytes = &ArrayBuffer::__WARNING__UNSAFE__ACCESS_VIOLATION__UNSAFE__($this->buffer);

        return \ord($bytes[$offset]);
    }

    public function fSet(int $offset, $value): void
    {
        $bytes = &ArrayBuffer::__WARNING__UNSAFE__ACCESS_VIOLATION__UNSAFE__($this->buffer);
        $bytes[$offset] = \chr($value);
    }
}
