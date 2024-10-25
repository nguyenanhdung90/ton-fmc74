<?php

namespace App\Http\Controllers;

use App\TON\Withdraws\WithdrawMemoToMemoInterface;
use App\TON\Withdraws\WithdrawTonV4R2Interface;
use App\TON\Withdraws\WithdrawUSDTV4R2Interface;
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

    public function withdrawTON(Request $request): string
    {
        $destinationAddress = '0QB2qumdPNrPUzgAAuTvG43NNBg45Cl4Bi_Gt81vE-EwF70k';
        $this->withdrawTon->process($destinationAddress, "0.11", 'comment');
        return 'success';
    }

    public function withdrawUSDT(Request $request): string
    {
        $destinationAddress = '0QB2qumdPNrPUzgAAuTvG43NNBg45Cl4Bi_Gt81vE-EwF70k';
        $this->withdrawUSDT->process($destinationAddress, "0.3", 'plus usdt');
        return 'success';
    }
}
