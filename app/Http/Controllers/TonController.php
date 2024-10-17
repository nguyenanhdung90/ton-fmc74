<?php

namespace App\Http\Controllers;

use App\TON\HttpClients\TonCenterClientInterface;
use App\TON\Interop\Address;
use App\TON\Interop\Boc\Cell;
use App\TON\Interop\Bytes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class TonController extends Controller
{

    public function rpc(TonCenterClientInterface $tonCenterV2Client)
    {
        $params = [
            "method" => "getTransactions",
            "params" => [
                "address" => config('services.ton.root_ton_wallet'),
                "limit" => 200,
                "archival" => true
            ],
            "id" => "string1",
            "jsonrpc" => "2.0"
        ];
        $data = $tonCenterV2Client->jsonRPC($params);
        $result = Arr::get($data, 'result');
        $sources = [];
        foreach ($result as $item) {
            $sources[] = Arr::get($item, 'in_msg.source');
        }
        $uniqueSources = array_unique(array_filter($sources));
        $walletSources = [];
        foreach ($uniqueSources as $address) {
            $address = new Address($address);
            $walletSources[] = $address->toString(false, true, true, true);
        }
        var_dump($walletSources);
        $dataJettonWallets = $tonCenterV2Client->getJettonWallets([
            'address' => implode(',', $walletSources),
            'limit' => count($walletSources),
            'offset' => 0
        ]);
        var_dump($dataJettonWallets);;
        $jettonWallets = $dataJettonWallets['jetton_wallets'];
        $jettonMasters = Arr::pluck($jettonWallets, 'jetton');
        var_dump($jettonMasters);
        sleep(1);
        $data = $tonCenterV2Client->getJettonMasters([
            'address' => implode(',', $jettonMasters),
            'limit' => count($jettonMasters),
            'offset' => 0
        ]);
        //Log::info($result);
        return var_dump($data);
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

    public function test() {
        $address = new Address('EQBo8k3byv2sq9A4uG0d89mxL2LlETwD0wE-7DlynP6KNMDi');
        return $address->toString(true, true, false, true);
    }
}
