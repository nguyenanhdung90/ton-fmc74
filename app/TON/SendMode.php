<?php declare(strict_types=1);

namespace App\TON;

class SendMode
{
    const CARRY_ALL_REMAINING_BALANCE = 128;
    const CARRY_ALL_REMAINING_INCOMING_VALUE = 64;
    const DESTROY_ACCOUNT_IF_ZERO = 32;
    const PAY_GAS_SEPARATELY = 1;
    const IGNORE_ERRORS = 2;
    const NONE = 0;

    public static function combine(array $modes)
    {
        return array_sum($modes);
    }
}
