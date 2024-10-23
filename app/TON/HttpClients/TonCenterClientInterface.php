<?php

namespace App\TON\HttpClients;

use Illuminate\Support\Collection;

interface TonCenterClientInterface
{
    public const MAIN_BASE_URI = "https://toncenter.com/";

    public const TEST_BASE_URI = "https://testnet.toncenter.com/";

    public function jsonRPC(array $query);

    public function getTransactionJsonRPC(array $params): Collection;

    public function getJetWallets(array $params): ?Collection;

    public function getJetMasters(array $params): ?Collection;
}
