<?php

namespace App\TON\Transactions\apiV2;

use App\TON\Transactions\CollectAttribute;
use App\TON\Transactions\TransactionHelper;
use Illuminate\Support\Arr;

class CollectTotalFeesAttribute extends CollectAttribute
{
    public function collect(array $data): array
    {
        $parentTrans = parent::collect($data);
        $trans['total_fees'] = (int)Arr::get($data, 'fee');
        return array_merge($parentTrans, $trans);
    }
}
