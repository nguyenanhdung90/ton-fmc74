<?php

namespace App\TON\HttpClients;

interface TonCenterClientInterface
{
    public function jsonRPC(array $query);

    public function getJettonWallets(array $params);

    public function getJettonMasters(array $params);
}
