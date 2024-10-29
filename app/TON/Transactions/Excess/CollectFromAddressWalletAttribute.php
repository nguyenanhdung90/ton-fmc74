<?php

namespace App\TON\Transactions\Excess;

use App\TON\Transactions\CollectAttribute;
use Illuminate\Support\Arr;

class CollectFromAddressWalletAttribute extends CollectAttribute
{
    public function collect(array $data): array
    {
        $parentTrans = parent::collect($data);
        $trans['from_address_wallet'] = Arr::get($data, 'in_msg.source');
        return array_merge($parentTrans, $trans);
    }
}
