<?php

namespace App\TON\Withdraws;

use App\TON\Contracts\Exceptions\ContractException;
use App\TON\Contracts\Jetton\JettonMinter;
use App\TON\Contracts\Jetton\JettonWallet;
use App\TON\Contracts\Jetton\JettonWalletOptions;
use App\TON\Contracts\Jetton\TransferJettonOptions;
use App\TON\Contracts\Wallets\Transfer;
use App\TON\Contracts\Wallets\TransferOptions;
use App\TON\Exceptions\TransportException;
use App\TON\Exceptions\WithdrawTonException;
use App\TON\Interop\Address;
use App\TON\Interop\Boc\Exceptions\BitStringException;
use App\TON\Interop\Boc\SnakeString;
use App\TON\Interop\Units;
use App\TON\Mnemonic\Exceptions\TonMnemonicException;
use App\TON\Mnemonic\TonMnemonic;
use App\TON\SendMode;
use App\TON\Transactions\TransactionHelper;
use App\TON\Transports\Toncenter\Models\TonResponse;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

abstract class WithdrawUSDTAbstract extends WithdrawAbstract
{
    protected function getRootUSDT()
    {
        return config('services.ton.is_main') ? config('services.ton.root_usdt_main') :
            config('services.ton.root_usdt_test');
    }

    /**
     * @throws BitStringException
     * @throws TonMnemonicException
     * @throws ContractException
     * @throws TransportException
     * @throws WithdrawTonException
     */
    public function process(string $fromMemo, string $destAddress, string $transferAmount, string $toMemo = "")
    {
        $this->isValidWalletTransferAmount($fromMemo, $transferAmount,
            TransactionHelper::USDT, Units::USDt);
        $data = [
            'from_address_wallet' => config('services.ton.root_ton_wallet'),
            'from_memo' => $fromMemo,
            'type' => TransactionHelper::WITHDRAW,
            'to_memo' => $toMemo,
            'to_address_wallet' => $destAddress,
            'amount' => (string)Units::toNano($transferAmount, Units::USDt),
            'currency' => TransactionHelper::USDT,
            'decimals' => Units::USDt,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
        $lastInsertedId = DB::table('wallet_ton_transactions')->insertGetId($data);

        $phrases = config('services.ton.ton_mnemonic');
        $kp = TonMnemonic::mnemonicToKeyPair(explode(" ", $phrases));
        $wallet = $this->getWallet($kp->publicKey);
        /** @var Address $walletAddress */
        $walletAddress = $wallet->getAddress();
        $transport = $this->getTransport();
        $usdtRoot = JettonMinter::fromAddress(
            $transport,
            new Address($this->getRootUSDT())
        );
        $usdtWalletAddress = $usdtRoot->getJettonWalletAddress($transport, $walletAddress);
        $usdtWallet = new JettonWallet(new JettonWalletOptions(
            null, 0, $usdtWalletAddress
        ));
        $transfer = new TransferOptions((int)$wallet->seqno($transport));
        $extMessage = $wallet->createTransferMessage([
            new Transfer(
                $usdtWalletAddress,
                Units::toNano("0.1"),
                $usdtWallet->createTransferBody(
                    new TransferJettonOptions(
                        Units::toNano($transferAmount, Units::USDt),
                        new Address($destAddress),
                        $walletAddress,
                        $lastInsertedId,
                        SnakeString::fromString($toMemo)->cell(true),
                        Units::toNano("0.0000002")//100
                    )
                ),
                SendMode::combine([SendMode::PAY_GAS_SEPARATELY, SendMode::CARRY_ALL_REMAINING_INCOMING_VALUE,
                    SendMode::IGNORE_ERRORS])
            )],
            $transfer
        );
        $tonResponse = $transport->sendMessageReturnHash($extMessage, $kp->secretKey);
        $this->updateMsgHash($lastInsertedId, $tonResponse);
    }

    private function updateMsgHash(int $lastInsertedId, TonResponse $tonResponse)
    {
        if (empty($tonResponse->ok)) {
            // false withdraw
            return;
        }
        $result = $tonResponse->result;
        $msgHash = Arr::get($result, 'hash');
        if (empty($msgHash)) {
            // There is no hash message
            return;
        }
        DB::table('wallet_ton_transactions')->where('id', $lastInsertedId)
            ->update(['in_msg_hash' => $msgHash, 'query_id' => $lastInsertedId, 'updated_at' => Carbon::now()]);
    }
}
