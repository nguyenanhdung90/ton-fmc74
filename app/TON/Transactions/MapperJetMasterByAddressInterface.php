<?php

namespace App\TON\Transactions;

use Illuminate\Support\Collection;

interface MapperJetMasterByAddressInterface
{
    public function process(Collection $address): Collection;
}
