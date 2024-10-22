<?php declare(strict_types=1);

namespace App\TON\Contracts\Wallets\Simple;

use App\TON\Contracts\Wallets\AbstractWallet;
use App\TON\Contracts\Wallets\Wallet;

class SimpleWalletR1 extends AbstractWallet implements Wallet
{
    public static function getName(): string
    {
        return "simpleR1";
    }

    protected static function getHexCodeString(): string
    {
        return "B5EE9C72410101010044000084FF0020DDA4F260810200D71820D70B1FED44D0D31FD3FFD15112BAF2A122F901541044F910F2A2F80001D31F3120D74A96D307D402FB00DED1A4C8CB1FCBFFC9ED5441FDF089";
    }
}
