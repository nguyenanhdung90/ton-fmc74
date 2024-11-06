<?php

namespace App\Http\Controllers;

use App\TON\Withdraws\WithdrawTonV4R2Interface;
use App\TON\Withdraws\WithdrawUSDTV4R2Interface;
use Illuminate\Http\Request;

class TonController extends Controller
{
    private WithdrawTonV4R2Interface $withdrawTon;

    private WithdrawUSDTV4R2Interface $withdrawUSDT;

    public function __construct(
        WithdrawTonV4R2Interface $withdrawTon,
        WithdrawUSDTV4R2Interface $withdrawUSDT
    ) {
        $this->withdrawTon = $withdrawTon;
        $this->withdrawUSDT = $withdrawUSDT;
    }

    public function withdrawTONExample(Request $request): string
    {
        try {
            $destinationAddress = '0QB2qumdPNrPUzgAAuTvG43NNBg45Cl4Bi_Gt81vE-EwF70k';
            $this->withdrawTon->process('memo', $destinationAddress, 0.077, 'memo', false);
            return 'success';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function withdrawUSDTExample(Request $request): string
    {
        try {
            $destinationAddress = '0QB2qumdPNrPUzgAAuTvG43NNBg45Cl4Bi_Gt81vE-EwF70k';
            $this->withdrawUSDT->process('memo', $destinationAddress, 0.128, 'memo2', false);
            return 'success';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
