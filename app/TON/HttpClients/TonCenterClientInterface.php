<?php

namespace App\TON\HttpClients;

use Illuminate\Support\Collection;

interface TonCenterClientInterface
{
    public function jsonRPC(array $query);

    public function getTransactionJsonRPC(array $params): Collection;

    public function getJetWallets(array $params): ?Collection;

    public function getJetMasters(array $params): ?Collection;
}
