<?php declare(strict_types=1);

namespace App\TON\Contracts\Interfaces;

use App\TON\Interop\Address;
use App\TON\Contracts\Messages\StateInit;

interface Deployable
{
    public function getStateInit(): StateInit;

    public function getAddress(): Address;
}
