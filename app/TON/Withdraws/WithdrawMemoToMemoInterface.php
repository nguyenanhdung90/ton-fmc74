<?php

namespace App\TON\Withdraws;

interface WithdrawMemoToMemoInterface
{
    public function transfer(string $fromMemo, string $toMemo, int $amount, string $currency);
}
