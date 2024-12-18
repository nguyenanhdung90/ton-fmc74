<?php

namespace App\TON\Transactions\Excess;

use App\TON\Transactions\CollectAttribute;
use Illuminate\Support\Arr;

class CollectOccurTonAttribute extends CollectAttribute
{
    public function collect(array $data): array
    {
        // balance change = in_msg.value - fee
        $parentTrans = parent::collect($data);
        $trans['occur_ton'] = Arr::get($data, 'fee');
        return array_merge($parentTrans, $trans);
    }
}
