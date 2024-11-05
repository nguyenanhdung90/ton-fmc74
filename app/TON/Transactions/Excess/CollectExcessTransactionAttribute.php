<?php

namespace App\TON\Transactions\Excess;

use App\TON\Interop\Units;
use App\TON\Transactions\CollectAttributeInterface;
use App\TON\Transactions\TransactionHelper;
use Carbon\Carbon;

class CollectExcessTransactionAttribute implements CollectAttributeInterface
{
    public function collect(array $data): array
    {
        return [
            'hash' => null,
            'lt' => null,
            'occur_ton' => null,
            'from_address_wallet' => null,
            'to_address_wallet' => null,
            'amount' => null,
            'query_id' => null,
            'decimals' => Units::DEFAULT,
            'type' => TransactionHelper::WITHDRAW_EXCESS,
            'status' => TransactionHelper::SUCCESS,
            'currency' => TransactionHelper::TON,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
