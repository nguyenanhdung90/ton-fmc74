<?php

namespace App\TON\Withdraws;

interface WithdrawJettonInterface
{
    public function process(string $fromMemo, string $destAddress, string $transferAmount,
                            string $toMemo = "", bool $isAllRemainBalance = false);
}
