<?php declare(strict_types=1);

namespace App\TON\Contracts\Wallets\V4;

use Brick\Math\BigInteger;
use App\TON\Interop\Address;
use App\TON\Interop\Boc\Cell;
use App\TON\Contracts\Messages\StateInit;

class PluginDeployOptions
{
    public Address $dstAddress;
    public int $seqno;
    public int $pluginWc;
    public BigInteger $pluginBalance;
    public StateInit $pluginStateInit;
    public Cell $pluginMsgBody;
    public ?int $expireAt;

    public function __construct(
        Address $dstAddress,
        int $seqno,
        int $pluginWc,
        BigInteger $pluginBalance,
        StateInit $pluginStateInit,
        Cell $pluginMsgBody,
        ?int $expireAt = null
    ) {
        $this->dstAddress = $dstAddress;
        $this->seqno = $seqno;
        $this->pluginWc = $pluginWc;
        $this->pluginBalance = $pluginBalance;
        $this->pluginStateInit = $pluginStateInit;
        $this->pluginMsgBody = $pluginMsgBody;
        $this->expireAt = $expireAt;
    }
}
