<?php declare(strict_types=1);

namespace App\TON\Contracts\Jetton;

use Brick\Math\BigInteger;
use App\TON\Interop\Address;
use App\TON\Interop\Boc\Cell;
use App\TON\Interop\Bytes;

class JettonData
{
    public function __construct(
        BigInteger $totalSupply,
        bool $isMutable,
        ?Address $adminAddress,
        ?string $jettonContentUrl,
        ?Cell $jettonContentCell,
        ?Cell $jettonWalletCode,
    ) {}

    /**
     * @throws \App\TON\Interop\Boc\Exceptions\CellException
     */
    public function asPrintableArray(): array
    {
        return [
            "totalSupply" => $this->totalSupply->toBase(10),
            "isMutable" => $this->isMutable,
            "adminAddress" => !empty($this->adminAddress) ? $this->adminAddress->toString(true, true, false) : null,
            "jettonContentUrl" => $this->jettonContentUrl,
            "jettonContentCell" => $this->jettonContentCell
                ? Bytes::bytesToBase64($this->jettonContentCell->toBoc(false))
                : null,
            "jettonWalletCode" => $this->jettonWalletCode
                ? Bytes::bytesToBase64($this->jettonWalletCode->toBoc(false))
                : null,
        ];
    }
}
