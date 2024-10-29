<?php

namespace App\TON\Withdraws;

interface WithdrawMemoToMemoInterface
{
    public function transfer(string $fromMemo, string $toMemo, float $amount, string $currency, int $decimals);
}
