<?php declare(strict_types=1);

namespace App\TON\Marshalling\Tvm;

use Brick\Math\BigInteger;

if (!function_exists("slice")) {
    function slice(\App\TON\Interop\Boc\Slice $data): Slice
    {
        return new Slice($data);
    }
}

if (!function_exists("cell")) {
    function cell(\App\TON\Interop\Boc\Cell $data): Cell
    {
        return new Cell($data);
    }
}

if (!function_exists("num")) {
    function num($data): Number
    {
        return new Number($data);
    }
}
