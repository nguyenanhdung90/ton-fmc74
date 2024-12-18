<?php

namespace App\TON\Transactions\Deposit;

use App\TON\Transactions\CollectAttributeInterface;
use App\TON\TonHelper;
use Carbon\Carbon;

class CollectTransactionAttribute implements CollectAttributeInterface
{
    public function collect(array $data): array
    {
        return [
            'hash' => null,
            'lt' => null,
            'occur_ton' => null,
            'from_address_wallet' => null,
            'to_memo' => null,
            'to_address_wallet' => config('services.ton.root_wallet'),
            'amount' => null,
            'type' => TonHelper::DEPOSIT,
            'status' => TonHelper::SUCCESS,
            'currency' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
