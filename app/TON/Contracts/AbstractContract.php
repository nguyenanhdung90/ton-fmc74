<?php declare(strict_types=1);

namespace App\TON\Contracts;

use App\TON\Interop\Address;
use App\TON\Interop\Boc\Cell;
use App\TON\Interop\Boc\Exceptions\CellException;
use App\TON\Interop\Bytes;
use App\TON\Contract;
use App\TON\Contracts\Exceptions\ContractException;
use App\TON\Contracts\Interfaces\Deployable;
use App\TON\Contracts\Messages\Exceptions\MessageException;
use App\TON\Contracts\Messages\StateInit;
use App\TON\Contracts\Wallets\Exceptions\WalletException;

abstract class AbstractContract implements Contract, Deployable
{
    protected ?Cell $code = null;

    protected ?Cell $data = null;

    private ?Address $address;

    private int $wc;

    public function __construct(ContractOptions $contractOptions)
    {
        $this->address = $contractOptions->address;
        $this->wc = $contractOptions->workchain;
    }

    public function getCode(): Cell
    {
        if (!$this->code) {
            $this->code = $this->createCode();
        }

        return $this->code;
    }

    public function getData(): Cell
    {
        if (!$this->data) {
            $this->data = $this->createData();
        }

        return $this->data;
    }

    public function getAddress(): Address
    {
        if (!$this->address) {
            try {
                $stateCell = $this->getStateInit()->cell();
                $this->address = new Address($this->getWc() . ":" . Bytes::bytesToHexString($stateCell->hash()));

            } catch (MessageException | CellException $e) {
                throw new WalletException("Address calculation error: " . $e->getMessage(), $e->getCode(), $e);
            }

        }

        return $this->address;
    }

    /**
     * @throws ContractException
     */
    public function getStateInit(): StateInit
    {
        return new StateInit(
            $this->getCode(),
            $this->getData(),
        );
    }

    public function getWc(): int
    {
        return $this->wc;
    }

    /**
     * @throws ContractException
     */
    protected abstract function createCode(): Cell;

    /**
     * @throws ContractException
     */
    protected abstract function createData(): Cell;

    /**
     * @throws ContractException
     */
    protected static function deserializeCode(string $serializedBoc, bool $isBase64 = false): Cell
    {
        try {
            return Cell::oneFromBoc($serializedBoc, $isBase64);

        } catch (CellException $e) {
            throw new WalletException("Smartcontract code creation error: " . $e->getMessage(), $e->getCode(), $e);
        }

    }
}
