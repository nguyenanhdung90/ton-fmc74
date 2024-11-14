<?php

namespace App\TON\Transactions\Deposit;

use App\TON\Contracts\Exceptions\ContractException;
use App\TON\Contracts\Jetton\JettonMinter;
use App\TON\Exceptions\InvalidJettonException;
use App\TON\Exceptions\InvalidTonException;
use App\TON\Exceptions\TransportException;
use App\TON\Interop\Address;
use App\TON\Interop\Boc\Cell;
use App\TON\Interop\Boc\Exceptions\CellException;
use App\TON\Interop\Boc\Exceptions\SliceException;
use App\TON\Interop\Bytes;
use App\TON\TonHelper;
use App\TON\Transactions\CollectAttribute;
use Illuminate\Support\Arr;

class CollectMemoSenderAmountAttribute extends CollectAttribute
{
    /**
     * @throws SliceException
     * @throws InvalidJettonException
     * @throws CellException
     * @throws TransportException
     * @throws ContractException
     * @throws InvalidJettonException
     * @throws InvalidTonException
     */
    public function collect(array $data): array
    {
        $parentTrans = parent::collect($data);
        $sender = new Address(Arr::get($data, 'in_msg.source'));
        if (!empty(Arr::get($data, 'in_msg.source_details.jetton_master'))) {
            $body = TonHelper::parseJetBody(Arr::get($data, 'in_msg.msg_data.body'));
            $amount = $body->get('amount', 0);
            $memo = $body->get('comment');
            /** @var Address $fromAddress */
            $fromAddress = $body->get('from_address');
            $hexJettonMaster = Arr::get($data, 'in_msg.source_details.jetton_master.hex_address');
            $this->validJettonSender($sender, new Address($hexJettonMaster));
            $fromAddressWallet = $fromAddress->asWallet(!config('services.ton.is_main'));
        } else {
            $amount = (int)Arr::get($data, 'in_msg.value');
            $address = new Address($sender);
            $fromAddressWallet = $address->asWallet(!config('services.ton.is_main'));
            $memo = $this->validTonDepositGetComment(Arr::get($data, 'in_msg'));
        }

        Arr::set($trans, 'to_memo', $memo);
        Arr::set($trans, 'from_address_wallet', $fromAddressWallet);
        Arr::set($trans, 'amount', $amount);
        return array_merge($parentTrans, $trans);
    }

    /**
     * @throws InvalidTonException
     * @throws CellException
     * @throws SliceException
     */
    private function validTonDepositGetComment(array $inMsg): string
    {
        if (Arr::get($inMsg, 'msg_data.@type') === 'msg.dataText') {
            return Arr::get($inMsg, 'message');
        }
        if (Arr::get($inMsg, 'msg_data.@type') !== 'msg.dataRaw') {
            throw new InvalidTonException("Invalid deposit ton msg");
        }
        if (empty(Arr::get($inMsg, 'msg_data.body'))) {
            throw new InvalidTonException("Invalid, body is empty");
        }
        $body = Arr::get($inMsg, 'msg_data.body');
        $bytes = Bytes::base64ToBytes($body);
        $cell = Cell::oneFromBoc($bytes, true);
        $slice = $cell->beginParse();
        $remainBit = count($slice->getRemainingBits());
        if ($remainBit >= 32) {
            throw new InvalidTonException("simple message without comment need opcode is zero");
        }
        return "";
    }

    /**
     * @throws TransportException
     * @throws ContractException
     * @throws InvalidJettonException
     */
    private function validJettonSender(Address $sender, Address $masterJetton)
    {
        $transport = TonHelper::getTransport();
        $minRoot = JettonMinter::fromAddress($transport, $masterJetton);
        $rootWallet = new Address(config("services.ton.root_wallet"));
        sleep(1);
        $validSender = $minRoot->getJettonWalletAddress($transport, $rootWallet);
        if (!$validSender->isEqual($sender)) {
            $msg = printf("Jetton sender is fake: %s, validSender: %s \n",
                $sender->asWallet(!config("services.ton.is_main")), $validSender->asWallet(!config("services.ton.is_main")));
            throw new InvalidJettonException($msg);
        }
    }
}
