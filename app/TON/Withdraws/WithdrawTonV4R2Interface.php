<?php

namespace App\TON\Withdraws;

interface WithdrawTonV4R2Interface
{
    public function process(string $fromMemo, string $toAddress, string $transferAmount, string $toMemo = "");
}
