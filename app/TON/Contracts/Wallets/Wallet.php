<?php declare(strict_types=1);

namespace App\TON\Contracts\Wallets;

use App\TON\Interop\Boc\Cell;
use App\TON\Contract;
use App\TON\Contracts\Interfaces\Deployable;
use App\TON\Contracts\Messages\ExternalMessage;
use App\TON\Contracts\Wallets\Exceptions\WalletException;
use App\TON\Transport;
use App\TON\TypedArrays\Uint8Array;

interface Wallet extends Contract, Deployable
{
    /**
     * @param Transfer[] $transfers
     * @throws WalletException
     */
    public function createTransferMessage(array $transfers, ?TransferOptions $options = null): ExternalMessage;

    /**
     * @throws WalletException
     */
    public function seqno(Transport $transport): ?int;

    /**
     * @throws WalletException
     */
    public function createSigningMessage(int $seqno): Cell;

    public function getPublicKey(): Uint8Array;

    /**
     * @throws WalletException
     */
    public static function getCodeHash(): string;
}
