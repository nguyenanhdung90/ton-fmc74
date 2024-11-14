<?php

namespace App\Http\Controllers;

use App\TON\Exceptions\InvalidJettonException;
use App\TON\Interop\Boc\Cell;
use App\TON\Interop\Bytes;
use App\TON\TonHelper;
use App\TON\Transactions\JettonBodyMsg;
use App\TON\Withdraws\WithdrawJettonInterface;
use App\TON\Withdraws\WithdrawTonV4R2Interface;
use Illuminate\Http\Request;

class TonController extends Controller
{
    private WithdrawTonV4R2Interface $withdrawTon;
    private WithdrawJettonInterface $withdrawJetton;

    public function __construct(
        WithdrawTonV4R2Interface $withdrawTon,
        WithdrawJettonInterface $withdrawJetton
    ) {
        $this->withdrawTon = $withdrawTon;
        $this->withdrawJetton = $withdrawJetton;
    }

    public function withdrawTONExample(Request $request): string
    {
        try {
            $destinationAddress = '0QB2qumdPNrPUzgAAuTvG43NNBg45Cl4Bi_Gt81vE-EwF70k';
            $this->withdrawTon->process('memo', $destinationAddress, 20, 'memo2', false);
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
                0.0119, 'memo2', false);
            return 'success';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function valid()
    {
        //return http_build_query(["msg_hash" => "5+tEEKu8x6Xlm/+dy+RMPihxugH6PgRj3T/ZtjDXUKM="]);
        // failed usdt withdraw 09375cfc
        //$body = "te6cckECBAEAAQ0AAZwJN1z8QYaYZHJBvRRKG7nVgefZBtwWVu+jd6RG9sjcITma9/+gmN7s+iuTW0v+J/o8zc4+xJ
        //+rXucraF786akGKamjF2c0YgQAAAD1AEIBAatIAdvk5N0RUJnUmfvqEdwPS66o7KDIG/OjbUrIJjNBMQgdABoTi1kR6AvQmrUxJYIzs0dbGuYlHt2PzIjKMv47ULBOkBfXhAAAAAAAAAAAAAAAAAAAwAIBqg+KfqUABnNGHGPEJ0AcnDgIAO1V0zp5tZ6mcAAFyd43G5poMHHIUvAMX41vmt4nwmAvADt8nJuiKhM6kz99QjuB6XXVHZQZA350balZBMZoJiEDggMDABIAAAAAbWVtbzL0fV6f";

        // Failed without ton  10737dd8 584db28c
        //$body = "te6cckEBAgEAswABnBBzfdjJ0BNNrLwJU4dmpHAHwGqWYuh1bWCukHi97FWvYKtv4yWiU
        //+yO4XuK7vGEfCJauInQKSxTBbtoBN6wUA8pqaMXZzR48wAAAPkAAwEAv0gB2+Tk3RFQmdSZ++oR3A9LrqjsoMgb86NtSsgmM0ExCB0AHaq6Z082s9TOAAC5O8bjc00GDjkKXgGL8a3zW8T4TAXUEqBfIAAAAAAAAAAAAAAAAAAAAAAAADaytreZQCQuxak=";

        // without usdt memo comment  7362d09c
        //$body ="te6cckEBAgEAPgABYnNi0JxUbeTvzoaY/THJw4gA0eSbt5X7WVegcXDaO+ezYl7FyiJ4B6YCfdhy5Tn9FGkBABAAAAAAbWVtb
        ///pTz6s=";

        //success usdt deposit no comment
        //$body = "te6cckEBAQEAMwAAYnNi0JxUbeTvZJee/jKdMIgA0eSbt5X7WVegcXDaO+ezYl7FyiJ4B6YCfdhy5Tn9FGhv1hOR";

        // success ton deposit no comment
        $body = "te6cckEBAQEAAgAAAEysuc0=";

        //$bodyMsg = new JettonBodyMsg($body);
        return 123;
    }
}
