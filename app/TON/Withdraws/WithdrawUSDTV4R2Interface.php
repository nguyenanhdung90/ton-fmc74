<?php

namespace App\TON\Withdraws;

interface WithdrawUSDTV4R2Interface
{
    public function process(string $fromMemo, string $destAddress, string $transferAmount,
                            string $toMemo = "", bool $isAllRemainBalance = false);
}
