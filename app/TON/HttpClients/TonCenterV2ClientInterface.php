<?php

namespace App\TON\HttpClients;

interface TonCenterV2ClientInterface
{
    public function jsonRPC(array $params);
}
