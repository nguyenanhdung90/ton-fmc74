<?php

namespace App\TON\Transactions\Excess;

use App\TON\Transactions\CollectAttribute;
use Illuminate\Support\Arr;

class CollectAmountAttribute extends CollectAttribute
{
    public function collect(array $data): array
    {
        $parentTrans = parent::collect($data);
        $trans['amount'] = Arr::get($data, 'in_msg.value');
        return array_merge($parentTrans, $trans);
    }
}
