<?php

namespace App\TON\Withdraws;

interface WithdrawUSDTV4R2Interface
{
    public function process(string $destAddress, string $usdtAmount, string $comment = "");
}
