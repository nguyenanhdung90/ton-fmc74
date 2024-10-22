<?php declare(strict_types=1);

namespace App\TON\Contracts\Wallets;

use Brick\Math\BigInteger;
use App\TON\Interop\Address;
use App\TON\Interop\Boc\Cell;
use App\TON\Ton\Contracts\Messages\StateInit;
use App\TON\Ton\SendMode;

class Transfer
{
    public function __construct(
        Address $dest,
        BigInteger $amount,
        $payload = "",
        $sendMode = SendMode::NONE,
        ?StateInit $stateInit = null,
        bool $bounce = false,
    ) {
    }
}
