<?php

namespace App\TON\Transactions\Excess;

use App\TON\Interop\Boc\Cell;
use App\TON\Interop\Bytes;
use App\TON\Transactions\CollectAttribute;
use App\TON\Transactions\TransactionHelper;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class CollectQueryIdAttribute extends CollectAttribute
{
    public function collect(array $data): array
    {
        $parentTrans = parent::collect($data);
        $body = Arr::get($data, 'in_msg.msg_data.body');
        if (!$body) {
            return $parentTrans;
        }
        $trans['query_id'] = $this->parseQueryId($body);
        return array_merge($parentTrans, $trans);
    }

    private function parseQueryId(string $body): ?int
    {
        try {
            $bytes = Bytes::base64ToBytes($body);
            $cell = Cell::oneFromBoc($bytes, true);
            $slice = $cell->beginParse();
            $remainBit = count($slice->getRemainingBits());
            if ($remainBit < 32) {
                return null;
            }
            $opcode = Bytes::bytesToHexString($slice->loadBits(32));
            if ($opcode !== TransactionHelper::EXCESS_OPCODE) {
                return null;
            }
            $hexQueryId = Bytes::bytesToHexString($slice->loadBits(64));
            return hexdec($hexQueryId);
        } catch (\Exception $e) {
            Log::error('Exception parseQueryId : ' . $e->getMessage());
            return null;
        }
    }
}
