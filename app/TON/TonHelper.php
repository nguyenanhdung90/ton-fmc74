<?php

namespace App\TON;

class TonHelper
{
    /**
     * @throws \Exception
     */
    public static function random(int $length): string
    {
        $bytes = random_bytes($length);
        return bin2hex($bytes);
    }
}
