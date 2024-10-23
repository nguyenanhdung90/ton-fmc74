<?php declare(strict_types=1);

namespace App\TON;

//enum SendMode : int
//{
//    case CARRY_ALL_REMAINING_BALANCE = 128;
//    case CARRY_ALL_REMAINING_INCOMING_VALUE = 64;
//    case DESTROY_ACCOUNT_IF_ZERO = 32;
//    case PAY_GAS_SEPARATELY = 1;
//    case IGNORE_ERRORS = 2;
//    case NONE = 0;
//
//    public function combine(SendMode ...$otherMode): int
//    {
//        return array_reduce(
//            $otherMode,
//            static fn(int $carry, SendMode $mode) => $carry + $mode->value,
//            $this->value,
//        );
//    }
//}

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
