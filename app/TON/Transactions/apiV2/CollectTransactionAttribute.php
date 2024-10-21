<?php

namespace App\TON\Transactions\apiV2;

use App\TON\Transactions\CollectAttributeInterface;
use Carbon\Carbon;

class CollectTransactionAttribute implements CollectAttributeInterface
{
    public function collect(array $data): array
    {
        return [
            'hash' => null,
            'lt' => null,
            'total_fees' => null,
            'to_memo' => null,
            'from_address_wallet' => null,
            'amount' => null,
            'decimals' => null,
            'type' => config('services.ton.deposit'),
            'currency' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
