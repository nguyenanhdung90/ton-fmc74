<?php

namespace App\TON\Withdraws;

use App\TON\Contracts\Wallets\Exceptions\WalletException;
use App\TON\Contracts\Wallets\Transfer;
use App\TON\Contracts\Wallets\TransferOptions;
use App\TON\Contracts\Wallets\V4\WalletV4R2;
use App\TON\Exceptions\TransportException;
use App\TON\Interop\Address;
use App\TON\Interop\Units;
use App\TON\Mnemonic\Exceptions\TonMnemonicException;
use App\TON\Mnemonic\TonMnemonic;
use App\TON\SendMode;

abstract class WithdrawTonAbstract extends WithdrawAbstract
{
    /**
     * @throws WalletException
     * @throws TonMnemonicException
     * @throws TransportException
     */
    public function process(string $toAddress, string $tonAmount, string $comment = "")
    {
        $phrases = config('services.ton.ton_mnemonic');
        $transport = $this->getTransport();
        $kp = TonMnemonic::mnemonicToKeyPair(explode(" ", $phrases));
        /** @var WalletV4R2 $wallet */
        $wallet = $this->getWallet($kp->publicKey);
        $extMsg = $wallet->createTransferMessage(
            [
                new Transfer(
                     new Address($toAddress),
                     Units::toNano($tonAmount),
                     $comment,
                    SendMode::PAY_GAS_SEPARATELY
                )
            ],
            new TransferOptions((int)$wallet->seqno($transport))
        );
        $transport->sendMessage($extMsg, $kp->secretKey);
    }
}


