<?php

namespace App\Http\Controllers;

use App\TON\HttpClients\TonCenterV2ClientInterface;
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
        $b64String = 'te6cckEBAQEADgAAGNUydtsAAAAAAAAAAPfBmNw=';
        $bytes = Bytes::base64ToBytes($b64String);
        return gettype($bytes);
    }
}
