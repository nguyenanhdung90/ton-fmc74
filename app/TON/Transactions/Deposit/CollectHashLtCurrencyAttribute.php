<?php

namespace App\TON\Transactions\Deposit;

use App\TON\Transactions\CollectAttribute;
use App\TON\TonHelper;
use Illuminate\Support\Arr;

class CollectHashLtCurrencyAttribute extends CollectAttribute
{
    public function collect(array $data): array
    {
        $parentTrans = parent::collect($data);
        $currency = Arr::get($data, 'in_msg.source_details.jetton_master.currency');
        $trans = array(
            'hash' => Arr::get($data, 'transaction_id.hash'),
            'lt' => Arr::get($data, 'transaction_id.lt'),
            'currency' => $currency ?: TonHelper::TON,
        );
        return array_merge($parentTrans, $trans);
    }
}
