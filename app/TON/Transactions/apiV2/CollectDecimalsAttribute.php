<?php

namespace App\TON\Transactions\apiV2;

use App\TON\Interop\Units;
use App\TON\Transactions\CollectAttribute;
use Illuminate\Support\Arr;

class CollectDecimalsAttribute extends CollectAttribute
{
    public function collect(array $data): array
    {
        $parentTrans = parent::collect($data);
        $decimals = (int)Arr::get($data, 'in_msg.source_details.jetton_master.decimals');
        $trans['decimals'] = $decimals ?: Units::DEFAULT;
        return array_merge($parentTrans, $trans);
    }
}
