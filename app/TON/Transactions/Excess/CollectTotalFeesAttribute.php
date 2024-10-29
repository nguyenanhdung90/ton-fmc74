<?php

namespace App\TON\Transactions\Excess;

use App\TON\Transactions\CollectAttribute;
use Illuminate\Support\Arr;

class CollectTotalFeesAttribute extends CollectAttribute
{
    public function collect(array $data): array
    {
        $parentTrans = parent::collect($data);
        $trans['total_fees'] = Arr::get($data, 'fee');
        return array_merge($parentTrans, $trans);
    }
}
