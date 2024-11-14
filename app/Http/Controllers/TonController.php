<?php

namespace App\Http\Controllers;

use App\TON\TonHelper;
use App\TON\Withdraws\WithdrawJettonV4R2Interface;
use App\TON\Withdraws\WithdrawTonV4R2Interface;
use Illuminate\Http\Request;

class TonController extends Controller
{
    private WithdrawTonV4R2Interface $withdrawTon;
    private WithdrawJettonV4R2Interface $withdrawJetton;

    public function __construct(
        WithdrawTonV4R2Interface $withdrawTon,
        WithdrawJettonV4R2Interface $withdrawJetton
    ) {
        $this->withdrawTon = $withdrawTon;
        $this->withdrawJetton = $withdrawJetton;
    }

    public function withdrawTONExample(Request $request): string
    {
        try {
            $destinationAddress = '0QB2qumdPNrPUzgAAuTvG43NNBg45Cl4Bi_Gt81vE-EwF70k';
            $this->withdrawTon->process('memo', $destinationAddress, 20.132, 'memo2', false);
            return 'success';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function withdrawJettonExample(Request $request): string
    {
        try {
            $destinationAddress = '0QB2qumdPNrPUzgAAuTvG43NNBg45Cl4Bi_Gt81vE-EwF70k';
            $this->withdrawJetton->process(TonHelper::USDT, 'memo', $destinationAddress,
                20.74, 'memo2', false);
            return 'success';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
