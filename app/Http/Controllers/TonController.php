<?php

namespace App\Http\Controllers;

use App\TON\Withdraws\WithdrawAIOTXV4R2Interface;
use App\TON\Withdraws\WithdrawNOTV4R2Interface;
use App\TON\Withdraws\WithdrawTonV4R2Interface;
use App\TON\Withdraws\WithdrawUSDTV4R2Interface;
use Illuminate\Http\Request;

class TonController extends Controller
{
    private WithdrawTonV4R2Interface $withdrawTon;
    private WithdrawUSDTV4R2Interface $withdrawUSDT;
    private WithdrawAIOTXV4R2Interface $withdrawAIOTX;
    private WithdrawNOTV4R2Interface $withdrawNOT;

    public function __construct(
        WithdrawTonV4R2Interface $withdrawTon,
        WithdrawUSDTV4R2Interface $withdrawUSDT,
        WithdrawAIOTXV4R2Interface $withdrawAIOTX,
        WithdrawNOTV4R2Interface $withdrawNOT
    ) {
        $this->withdrawTon = $withdrawTon;
        $this->withdrawUSDT = $withdrawUSDT;
        $this->withdrawAIOTX = $withdrawAIOTX;
        $this->withdrawNOT = $withdrawNOT;
    }

    public function withdrawTONExample(Request $request): string
    {
        try {
            $destinationAddress = '0QDt8nJuiKhM6kz99QjuB6XXVHZQZA350balZBMZoJiEDsVA';
            $this->withdrawTon->process('memo', $destinationAddress, 0.0189, 'memo2', false);
            return 'success';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function withdrawUSDTExample(Request $request): string
    {
        try {
            $destinationAddress = '0QB2qumdPNrPUzgAAuTvG43NNBg45Cl4Bi_Gt81vE-EwF70k';
            $this->withdrawUSDT->process('memo', $destinationAddress, 0.0129, 'memo2', true);
            return 'success';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function withdrawNOTExample(Request $request): string
    {
        try {
            $destinationAddress = '0QB2qumdPNrPUzgAAuTvG43NNBg45Cl4Bi_Gt81vE-EwF70k';
            $this->withdrawNOT->process('memo', $destinationAddress, 0.0011, 'memo2', false);
            return 'success';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function withdrawAIOTXExample(Request $request): string
    {
        // Only test environment for this coin
        try {
            $destinationAddress = '0QB2qumdPNrPUzgAAuTvG43NNBg45Cl4Bi_Gt81vE-EwF70k';
            $this->withdrawAIOTX->process('memo', $destinationAddress, 0.0022, 'memo2', false);
            return 'success';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
