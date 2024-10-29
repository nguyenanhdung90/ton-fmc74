<?php

namespace App\TON\Transactions\Excess;

use App\TON\Transactions\CollectAttribute;
use Illuminate\Support\Arr;

class CollectToAddressWalletAttribute extends CollectAttribute
{
    public function collect(array $data): array
    {
        $parentTrans = parent::collect($data);
        $trans['to_address_wallet'] = Arr::get($data, 'in_msg.destination');
        return array_merge($parentTrans, $trans);
    }
}
