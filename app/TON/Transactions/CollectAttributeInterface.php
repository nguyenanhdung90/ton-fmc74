<?php

namespace App\TON\Transactions;

interface CollectAttributeInterface
{
    public function collect(array $data): array;
}
