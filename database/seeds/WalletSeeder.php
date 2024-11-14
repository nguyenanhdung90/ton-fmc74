<?php

use App\Models\Wallet;
use App\TON\Interop\Units;
use App\TON\TonHelper;
use Illuminate\Database\Seeder;

class WalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Wallet::updateOrCreate(
            [
                "user_name" => "user_1",
                "currency" => TonHelper::TON,
            ],
            [
                "amount" => 30000000000,
            ]
        );
        Wallet::updateOrCreate(
            [
                "user_name" => "user_1",
                "currency" => TonHelper::USDT,
            ],
            [
                "amount" => 30000000
            ]
        );
        Wallet::updateOrCreate(
            [
                "user_name" => "user_2",
                "currency" => TonHelper::TON,
            ],
            [
                "amount" => 30000000000
            ]
        );
        Wallet::updateOrCreate(
            [
                "user_name" => "user_2",
                "currency" => TonHelper::USDT,
            ],
            [
                "amount" => 30000000
            ]
        );
        Wallet::updateOrCreate(
            [
                "user_name" => "user_1",
                "currency" => TonHelper::PAYN,
            ],
            [
                "amount" => 50000000000
            ]
        );
        Wallet::updateOrCreate(
            [
                "user_name" => "user_2",
                "currency" => TonHelper::PAYN,
            ],
            [
                "amount" => 50000000000
            ]
        );
    }
}
