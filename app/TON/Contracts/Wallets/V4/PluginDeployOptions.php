<?php declare(strict_types=1);

namespace App\TON\Contracts\Wallets\V4;

use Brick\Math\BigInteger;
use App\TON\Interop\Address;
use App\TON\Interop\Boc\Cell;
use App\TON\Contracts\Messages\StateInit;

class PluginDeployOptions
{
    public function __construct(
        Address $dstAddress,
        int $seqno,
        int $pluginWc,
        BigInteger $pluginBalance,
        StateInit $pluginStateInit,
        Cell $pluginMsgBody,
        ?int $expireAt = null
    ) {}
}
