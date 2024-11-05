<?php

namespace App\TON\Transactions\Deposit;

use App\TON\Transactions\CollectAttribute;
use Illuminate\Support\Arr;

class CollectOccurTonAttribute extends CollectAttribute
{
    public function collect(array $data): array
    {
        $parentTrans = parent::collect($data);
        $symbol = Arr::get($data, 'in_msg.source_details.jetton_master.symbol');
        if ($symbol) {
            $trans['occur_ton'] = (int)Arr::get($data, 'fee') - (int)Arr::get($data, 'in_msg.value');
        } else {
            $trans['occur_ton'] = (int)Arr::get($data, 'fee');
        }
        return array_merge($parentTrans, $trans);
    }
}
