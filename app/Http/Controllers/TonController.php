<?php

namespace App\Http\Controllers;

use App\TON\Interop\Units;
use App\TON\Transactions\TransactionHelper;
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
        $this->withdrawMemoToMemo->transfer('memo', 'memo2', 0.00125, TransactionHelper::USDT, Units::USDt);
        return 'Success';
    }

    public function withdrawTONExample(Request $request): string
    {
        try {
            $destinationAddress = '0QB2qumdPNrPUzgAAuTvG43NNBg45Cl4Bi_Gt81vE-EwF70k';
            $this->withdrawTon->process('memo', $destinationAddress, 0.001639, 'comment');
            return 'success';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function withdrawUSDTExample(Request $request): string
    {
        try {
            $destinationAddress = '0QB2qumdPNrPUzgAAuTvG43NNBg45Cl4Bi_Gt81vE-EwF70k';
            $this->withdrawUSDT->process('memo', $destinationAddress, 0.002227, 'plus usdt');
            return 'success';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function parseJetBody(): int
    {
        return 1;
    }
}
