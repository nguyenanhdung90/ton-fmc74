<?php declare(strict_types=1);

namespace App\TON\Contracts\Wallets;

use Brick\Math\BigInteger;
use App\TON\Interop\Address;
use App\TON\Contracts\Messages\StateInit;
use App\TON\SendMode;

class Transfer
{
    public Address $dest;
    public BigInteger $amount;
    public $payload;
    public $sendMode;
    public $stateInit;
    public $bounce;

    public function __construct(
        Address $dest,
        BigInteger $amount,
        $payload = "",
        $sendMode = SendMode::NONE,
        ?StateInit $stateInit = null,
        bool $bounce = false
    ) {
        $this->dest = $dest;
        $this->amount = $amount;
        $this->payload = $payload;
        $this->sendMode = $sendMode;
        $this->stateInit = $stateInit;
        $this->bounce = $bounce;
    }
}
