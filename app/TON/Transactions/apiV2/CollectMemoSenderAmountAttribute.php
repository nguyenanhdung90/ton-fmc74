<?php

namespace App\TON\Transactions\apiV2;

use App\Exceptions\InvalidJettonException;
use App\TON\Interop\Address;
use App\TON\Interop\Boc\Cell;
use App\TON\Interop\Boc\Exceptions\CellException;
use App\TON\Interop\Boc\Exceptions\SliceException;
use App\TON\Interop\Bytes;
use App\TON\Transactions\CollectAttribute;
use Illuminate\Support\Arr;

class CollectMemoSenderAmountAttribute extends CollectAttribute
{
    /**
     * @throws SliceException
     * @throws InvalidJettonException
     * @throws CellException
     */
    public function collect(array $data): array
    {
        $parentTrans = parent::collect($data);
        $bodyCode = Arr::get($data, 'in_msg.msg_data.body');
        if ($bodyCode) {
            $body = $this->parseJetBody($bodyCode);
            $amount = (int)Arr::get($body, 'amount', 0);
            $fromAddressWallet = Arr::get($body, 'from_address_wallet');
            $memo = Arr::get($body, 'comment');
        } else {
            $amount = (int)Arr::get($data, 'in_msg.value');
            $source = Arr::get($data, 'in_msg.source');
            $address = new Address($source);
            $fromAddressWallet = $address->asWallet(!config('services.tom.is_main'));
            $memo = Arr::get($data, 'in_msg.message_content.decoded.comment');
        }

        Arr::set($trans, 'to_memo', $memo);
        Arr::set($trans, 'from_address_wallet', $fromAddressWallet);
        Arr::set($trans, 'amount', $amount);
        return array_merge($parentTrans, $trans);
    }


    /**
     * @throws SliceException
     * @throws InvalidJettonException
     * @throws CellException
     */
    private function parseJetBody(string $body): array
    {
        $bytes = Bytes::base64ToBytes($body);
        $cell = Cell::oneFromBoc($bytes, true);
        $slice = $cell->beginParse();
        $remainBit = count($slice->getRemainingBits());
        if ($remainBit < 32) {
            throw new InvalidJettonException("Invalid Jetton.", InvalidJettonException::INVALID_JETTON);
        }
        $opcode = Bytes::bytesToHexString($slice->loadBits(32));
        if ($opcode != config('services.ton.jetton_opcode')) {
            throw new InvalidJettonException("Invalid Jetton notify",
                InvalidJettonException::INVALID_JETTON_OPCODE);
        }
        $slice->skipBits(64);
        $amount = $slice->loadCoins();
        $sender = $slice->loadAddress()->toString(true, true, null, true);
        $comment = null;
        if ($cellForward = $slice->loadMaybeRef()) {
            $forwardPayload = $cellForward->beginParse();
            $comment = $forwardPayload->loadString();
        }
        return [
            'amount' => (string)$amount,
            'from_address_wallet' => $sender,
            'comment' => $comment,
        ];
    }
}
