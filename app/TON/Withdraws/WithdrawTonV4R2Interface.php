<?php

namespace App\TON\Withdraws;

interface WithdrawTonV4R2Interface
{
    public function process(string $toAddress, string $tonAmount, string $comment = "");
}
