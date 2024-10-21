<?php

namespace App\Http\Controllers;

use App\TON\Withdraws\WithdrawMemoToMemoInterface;
use Illuminate\Http\Request;

class TonController extends Controller
{
    private WithdrawMemoToMemoInterface $withdrawMemoToMemo;

    public function __construct(
        WithdrawMemoToMemoInterface $withdrawMemoToMemo
    ) {
        $this->withdrawMemoToMemo = $withdrawMemoToMemo;
    }

    public function withdrawOnlyMemo(Request $request): string
    {
        $this->withdrawMemoToMemo->transfer('https://t.me/testgiver_ton_bot', 'plus', 0, 'TON');
        return 'Success';
    }
}
