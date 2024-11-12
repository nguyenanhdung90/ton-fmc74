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
        Wallet::firstOrCreate([
            "user_name" => "user_1",
            "amount" => 30000000000,
            "currency" => TonHelper::TON,
            "decimals" => Units::DEFAULT,
        ]);
        Wallet::firstOrCreate([
            "user_name" => "user_1",
            "amount" => 30000000,
            "currency" => TonHelper::USDT,
            "decimals" => Units::USDt,
        ]);
        Wallet::firstOrCreate([
            "user_name" => "user_2",
            "amount" => 30000000000,
            "currency" => TonHelper::TON,
            "decimals" => Units::DEFAULT,
        ]);
        Wallet::firstOrCreate([
            "user_name" => "user_2",
            "amount" => 30000000,
            "currency" => TonHelper::USDT,
            "decimals" => Units::USDt,
        ]);
        Wallet::firstOrCreate([
            "user_name" => "user_1",
            "amount" => 50000000000,
            "currency" => TonHelper::PAYN,
            "decimals" => Units::DEFAULT,
        ]);
        Wallet::firstOrCreate([
            "user_name" => "user_2",
            "amount" => 50000000000,
            "currency" => TonHelper::PAYN,
            "decimals" => Units::DEFAULT,
        ]);
    }
}
