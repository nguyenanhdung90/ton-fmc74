<?php declare(strict_types=1);

namespace App\TON;

use App\TON\Interop\Address;
use App\TON\Interop\Boc\Cell;
use App\TON\Contracts\Exceptions\ContractException;

interface Contract
{
    public static function getName(): string;

    /**
     * @throws ContractException
     */
    public function getCode(): Cell;

    /**
     * @throws ContractException
     */
    public function getData(): Cell;

    /**
     * @throws ContractException
     */
    public function getAddress(): Address;

    public function getWc(): int;
}
