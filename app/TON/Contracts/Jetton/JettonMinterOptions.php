<?php declare(strict_types=1);

namespace App\TON\Contracts\Jetton;

use App\TON\Interop\Address;
use App\TON\Interop\Boc\Cell;
use App\TON\Contracts\ContractOptions;

class JettonMinterOptions extends ContractOptions
{
    public ?Address $adminAddress;
    public ?string $jettonContentUrl;
    public Cell $jettonWalletCode;
    public ?Cell $code;
    public ?Address $address;
    public int $workchain;

    public function __construct(
        ?Address $adminAddress,
        ?string $jettonContentUrl,
        Cell $jettonWalletCode,
        ?Cell $code = null,
        ?Address $address = null,
        int $workchain = 0
    ) {
        $this->adminAddress = $adminAddress;
        $this->jettonContentUrl = $jettonContentUrl;
        $this->jettonWalletCode = $jettonWalletCode;
        $this->code = $code;
        $this->address = $address;
        $this->workchain = $workchain;
        parent::__construct($workchain, $address);
    }
}
