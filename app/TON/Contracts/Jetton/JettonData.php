<?php declare(strict_types=1);

namespace App\TON\Contracts\Jetton;

use Brick\Math\BigInteger;
use App\TON\Interop\Address;
use App\TON\Interop\Boc\Cell;
use App\TON\Interop\Bytes;

class JettonData
{
    public BigInteger $totalSupply;
    public bool $isMutable;
    public ?Address $adminAddress;
    public ?string $jettonContentUrl;
    public ?Cell $jettonContentCell;
    public ?Cell $jettonWalletCode;

    public function __construct(
        BigInteger $totalSupply,
        bool $isMutable,
        ?Address $adminAddress,
        ?string $jettonContentUrl,
        ?Cell $jettonContentCell,
        ?Cell $jettonWalletCode
    ) {
        $this->totalSupply = $totalSupply;
        $this->isMutable = $isMutable;
        $this->adminAddress = $adminAddress;
        $this->jettonContentUrl = $jettonContentUrl;
        $this->jettonContentCell = $jettonContentCell;
        $this->jettonWalletCode = $jettonWalletCode;
    }

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
