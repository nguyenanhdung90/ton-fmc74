<?php

namespace App\TON\Transactions\Deposit;

use App\TON\Transactions\CollectAttributeInterface;
use App\TON\Transactions\TransactionHelper;
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
            'to_address_wallet' => config('services.ton.root_ton_wallet'),
            'amount' => null,
            'decimals' => null,
            'type' => TransactionHelper::DEPOSIT,
            'status' => TransactionHelper::SUCCESS,
            'currency' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
