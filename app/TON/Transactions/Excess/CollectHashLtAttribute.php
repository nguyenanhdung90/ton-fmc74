<?php

namespace App\TON\Transactions\Excess;

use App\TON\Transactions\CollectAttribute;
use Illuminate\Support\Arr;

class CollectHashLtAttribute extends CollectAttribute
{
    public function collect(array $data): array
    {
        $parentTrans = parent::collect($data);
        $trans['hash'] = Arr::get($data, 'transaction_id.hash');
        $trans['lt'] = Arr::get($data, 'transaction_id.lt');
        return array_merge($parentTrans, $trans);
    }
}
