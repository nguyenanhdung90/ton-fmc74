<?php

namespace App\TON\Withdraws;

interface WithdrawJettonV4R2Interface
{
    public function process(string $currency, string $fromMemo, string $destAddress, string $transferAmount,
                            string $toMemo = "", bool $isAllRemainBalance = false);
}
