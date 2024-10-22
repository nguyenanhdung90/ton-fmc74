<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidJettonException;
use App\TON\Interop\Boc\Cell;
use App\TON\Interop\Bytes;
use App\TON\Withdraws\WithdrawMemoToMemoInterface;
use Illuminate\Http\Request;

class TonController extends Controller
{
    private WithdrawMemoToMemoInterface $withdrawMemoToMemo;

    public function __construct(
        WithdrawMemoToMemoInterface $withdrawMemoToMemo
    ) {
        $this->withdrawMemoToMemo = $withdrawMemoToMemo;
    }

    public function withdrawOnlyMemo(Request $request): string
    {
        $this->withdrawMemoToMemo->transfer('10', 'Usdt', 1, 'USDT');
        return 'Success';
    }

    /**
     * @throws \App\TON\Interop\Boc\Exceptions\SliceException
     * @throws \App\TON\Interop\Boc\Exceptions\CellException
     * @throws InvalidJettonException
     */
    public function parseJetBody(Request $request): array
    {
//$body = "te6cckEBAQEAPAAAdHNi0JwAAAAAAAA0pUC+vCAIAIdCfJslXcz5l1ihQkJ3+lH3qm9KfD0kdZkqmEXSqFaKAAAAAFRFU1RhivlG";// 200
$body = "te6cckEBAgEARAABYnNi0JxUbeTvVNl+jjAbWAgA0eSbt5X7WVegcXDaO+ezYl7FyiJ4B6YCfdhy5Tn9FGkBABwAAAAAUGx1cyB1c2R0dLP2WxA=";
        $bytes = Bytes::base64ToBytes($body);
        $cell = Cell::oneFromBoc($bytes, true);
        $slice = $cell->beginParse();
        $remainBit = count($slice->getRemainingBits());
        if ($remainBit < 32) {
            throw new InvalidJettonException("Invalid Jetton: " . $body, InvalidJettonException::INVALID_JETTON);
        }
        $op = $slice->loadBits(32);
        $opcode = Bytes::bytesToHexString($op);
        if ($opcode != config('services.ton.jetton_opcode')) {
            throw new InvalidJettonException("Invalid Jetton opcode: " . $body,
                InvalidJettonException::INVALID_JETTON_OPCODE);
        }
        $slice->skipBits(64);
        $amount = (string)$slice->loadCoins();
        $sender = $slice->loadAddress()->toString(true, true, null, true);
        $comment = null;
        $cellForward = $slice->loadMaybeRef();
        if ($cellForward) {
            $forwardPayload = $cellForward->beginParse();
            $comment = $forwardPayload->loadString();
        } else {
            $remainBitJet = count($slice->getRemainingBits());
            if ($remainBitJet >= 32) {
                $forwardOp = Bytes::bytesToHexString($slice->loadBits(32));
                if ($forwardOp == 0) {
                    $comment = $slice->loadString(32);
                }
            }
        }
        return [
            'amount' => (int)$amount,
            'from_address_wallet' => $sender,
            'comment' => $comment,
        ];
    }
}
