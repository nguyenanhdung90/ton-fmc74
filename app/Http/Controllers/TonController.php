<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidJettonException;
use App\TON\Interop\Boc\Cell;
use App\TON\Interop\Bytes;
use App\TON\Interop\Units;
use App\TON\Transactions\TransactionHelper;
use App\TON\Withdraws\WithdrawMemoToMemoInterface;
use App\TON\Withdraws\WithdrawTonV4R2Interface;
use App\TON\Withdraws\WithdrawUSDTV4R2Interface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TonController extends Controller
{
    private WithdrawMemoToMemoInterface $withdrawMemoToMemo;

    private WithdrawTonV4R2Interface $withdrawTon;

    private WithdrawUSDTV4R2Interface $withdrawUSDT;

    public function __construct(
        WithdrawMemoToMemoInterface $withdrawMemoToMemo,
        WithdrawTonV4R2Interface $withdrawTon,
        WithdrawUSDTV4R2Interface $withdrawUSDT
    ) {
        $this->withdrawMemoToMemo = $withdrawMemoToMemo;
        $this->withdrawTon = $withdrawTon;
        $this->withdrawUSDT = $withdrawUSDT;
    }

    public function withdrawInternalVirtualCurrencyExchange(Request $request): string
    {
        $this->withdrawMemoToMemo->transfer('10', 'Usdt', 1, 'USDT');
        return 'Success';
    }

    public function withdrawTONExample(Request $request): string
    {
        try {
            $destinationAddress = '0QB2qumdPNrPUzgAAuTvG43NNBg45Cl4Bi_Gt81vE-EwF70k';
            $this->withdrawTon->process('memo', $destinationAddress, 0.001122, 'comment');
            return 'success';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function withdrawUSDTExample(Request $request): string
    {
        try {
            $destinationAddress = '0QB2qumdPNrPUzgAAuTvG43NNBg45Cl4Bi_Gt81vE-EwF70k';
            $this->withdrawUSDT->process('memo', $destinationAddress, 0.002211, 'plus usdt');
            return 'success';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function parseJetBody(): int
    {
//        $body = "te6cckEBAQEADgAAGNUydtsAAAAAAAAE0YPCqrM=";
//        $bytes = Bytes::base64ToBytes($body);
//        $cell = Cell::oneFromBoc($bytes, true);
//        $slice = $cell->beginParse();
//        $remainBit = count($slice->getRemainingBits());
//        if ($remainBit < 32) {
//            throw new InvalidJettonException("Invalid Jetton, this is simple transfer TON: " . $body,
//                InvalidJettonException::INVALID_JETTON);
//        }
//        $opcode = Bytes::bytesToHexString($slice->loadBits(32));
//        $remainBit2 = count($slice->getRemainingBits());
//        $opcode2 = Bytes::bytesToHexString($slice->loadBits(64));

        $body = "te6cckEBAgEAtAABnCkkMVYSxjHhTWJG2kNlhutF+7zc6i+rjA+/QRtgx4ApnF/xZnfV3cggRuf0v1xEA7amkCWtJdox7ljTnrkY4gIpqaMXZx8ZmgAAAG0AAQEAwUgB2+Tk3RFQmdSZ++oR3A9LrqjsoMgb86NtSsgmM0ExCB0AHaq6Z082s9TOAAC5O8bjc00GDjkKXgGL8a3zW8T4TAXSpZ3OCAAAAAAAAAAAAAAAAAAAAAAAMbe2trK3OkAWSMA0";
        $bytes = Bytes::base64ToBytes($body);
        $cell = Cell::oneFromBoc($bytes, true);
        $slice = $cell->beginParse();
        $remainBit = count($slice->getRemainingBits());
        if ($remainBit < 32) {
            throw new InvalidJettonException("Invalid Jetton, this is simple transfer TON: " . $body,
                InvalidJettonException::INVALID_JETTON);
        }
        $opcode = Bytes::bytesToHexString($slice->loadBits(32));
        $remainBit2 = count($slice->getRemainingBits());
        $hexQueryId = Bytes::bytesToHexString($slice->loadBits(64));
        $dd = hexdec($hexQueryId);
        return 123;
    }
}
