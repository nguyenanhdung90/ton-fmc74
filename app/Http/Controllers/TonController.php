<?php

namespace App\Http\Controllers;

use App\TON\HttpClients\TonCenterV2ClientInterface;
use App\TON\Interop\Boc\Cell;
use App\TON\Interop\Bytes;
use Illuminate\Support\Facades\Log;

class TonController extends Controller
{

    public function rpc(TonCenterV2ClientInterface $tonCenterV2Client)
    {
        $params = [
            "method" => "getTransactions",
            "params" => [
                "address" => "0QDt8nJuiKhM6kz99QjuB6XXVHZQZA350balZBMZoJiEDsVA",
                "limit" => 200,
                "archival" => true
            ],
            "id" => "string1",
            "jsonrpc" => "2.0"
        ];

        $data = $tonCenterV2Client->jsonRPC($params);
        $result = $data['result'];
        Log::info($result);
        return print_r($result);
    }

    public function parse()
    {
        $b64String = 'te6cckEBAgEARAABYnNi0JxUbeTvVNl+jjAbWAgA0eSbt5X7WVegcXDaO+ezYl7FyiJ4B6YCfdhy5Tn9FGkBABwAAAAAUGx1cyB1c2R0dLP2WxA=';
        $bytes = Bytes::base64ToBytes($b64String);
        $cell = Cell::oneFromBoc($bytes, true);
        $originSlice = $cell->beginParse();
        $slice = clone $originSlice;
        $remainBit = count($slice->getRemainingBits());
        if ($remainBit > 32) {
            $op = Bytes::bytesToHexString($slice->loadBits(32));
            if ($op == 0) {
                echo "simple message";
            } elseif ($op == '7362d09c') {
                $slice->skipBits(64);
                $d = $slice->loadCoins();
                $address = $slice->loadAddress()->toString(true, true, null, true);
//                $loadBit = $slice->loadBit();
                $originCell = $slice->loadMaybeRef();
                $originForwardPayLoad = $originCell->beginParse();
                $forwardPayload = clone $originForwardPayLoad;
//                $forwardOp = Bytes::bytesToHexString($forwardPayload->loadBits(32));
                $remainBitfw = count($forwardPayload->getRemainingBits());
                $comment = $forwardPayload->loadString();
            }
        }
        return gettype($cell);
    }
}
