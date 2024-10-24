<?php declare(strict_types=1);

namespace App\TON\Contracts\Wallets\V4;

use App\TON\Interop\Boc\Cell;
use App\TON\Interop\Boc\Exceptions\BitStringException;
use App\TON\Contracts\Wallets\AbstractWallet;
use App\TON\Contracts\Wallets\Exceptions\WalletException;
use App\TON\Contracts\Wallets\Wallet;

abstract class WalletV4 extends AbstractWallet implements Wallet
{
    protected WalletV4Options $options;

    public function __construct(WalletV4Options $options)
    {
        $this->options = $options;
        parent::__construct($this->options);
    }

    public function createSigningMessage(int $seqno, ?int $expireAt = null, ?bool $withoutOp = null): Cell
    {
        try {
            $cell = new Cell();
            $bs = $cell->bits;
            $bs->writeUint($this->getWalletId(), 32);

            if ($seqno === 0) {
                for ($i = 0; $i < 32; $i++) {
                    $bs->writeBit(1);
                }
            } else {
                $expireAt = $expireAt ?? time() + 60;
                $bs->writeUint($expireAt, 32);
            }

            $bs->writeUint($seqno, 32);

            if (!$withoutOp) {
                $bs->writeUint(0, 8);
            }

            return $cell;

        } catch (BitStringException $e) {
            throw new WalletException($e->getMessage(), $e->getCode(), $e);
        }

    }

    protected function createData(): Cell
    {
        try {
            $cell = new Cell();
            $cell
                ->bits
                ->writeUint(0, 32)
                ->writeUint($this->getWalletId(), 32)
                ->writeBytes($this->getPublicKey())
                ->writeUint(0, 1);

            return $cell;

        } catch (BitStringException $e) {
            throw new WalletException("Wallet data creation error: " . $e->getMessage(), $e->getCode(), $e);
        }

    }

    protected function getWalletId(): int
    {
        return $this->options->walletId + $this->getWc();
    }
}
