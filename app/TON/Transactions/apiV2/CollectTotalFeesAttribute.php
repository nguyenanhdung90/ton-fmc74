<?php

namespace App\TON\Transactions\apiV2;

use App\TON\Transactions\CollectAttribute;
use Illuminate\Support\Arr;

class CollectTotalFeesAttribute extends CollectAttribute
{
    public function collect(array $data): array
    {
        $parentTrans = parent::collect($data);
        $symbol = Arr::get($data, 'in_msg.source_details.jetton_master.symbol');
        if ($symbol) {
            $trans['total_fees'] = (int)Arr::get($data, 'fee') - (int)Arr::get($data, 'in_msg.value');
        } else {
            $trans['total_fees'] = (int)Arr::get($data, 'fee');
        }
        return array_merge($parentTrans, $trans);
    }
}
