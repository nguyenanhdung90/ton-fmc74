<?php

namespace App\Http\Controllers;

use App\TON\Withdraws\WithdrawMemoToMemoInterface;
use App\TON\Withdraws\WithdrawTonV4R2Interface;
use App\TON\Withdraws\WithdrawUSDTV4R2Interface;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
            $this->withdrawTon->process('memo', $destinationAddress, "0.01", 'comment');
            return 'success';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function withdrawUSDTExample(Request $request): string
    {
        try {
            $destinationAddress = '0QB2qumdPNrPUzgAAuTvG43NNBg45Cl4Bi_Gt81vE-EwF70k';
            $this->withdrawUSDT->process('memo', $destinationAddress, "0.00193", 'plus usdt');
            return 'success';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function parseJetBody(): int
    {
        $date = Carbon::now();
        $date2 = Carbon::now()->subSeconds(10);
        $d = 11;
//        $result = ['hash' => 'u9UhGM1MK5zBiGjM2aUYrpRD/fW6+uUmmxVj/iF4ur4='];
//        $tonResponse = new TonResponse(true, $result, '', 1);
//        $d = (string)Units::toNano('0.00000011');
//        InsertWithdrawTonTransaction::dispatch($tonResponse, 'fff', 'ddgvcbre', 0.4534, 'fghcvbcvbc');
//        return 123;
    }
}
