<?php

namespace App\TON\HttpClients;

interface TonCenterClientInterface
{
    public function jsonRPC(array $params);

    public function getJettonWallets(array $params);

    public function getJettonMasters(array $params);
}
