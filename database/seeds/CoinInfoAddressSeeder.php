<?php

use App\Models\CoinInfoAddress;
use Illuminate\Database\Seeder;

class CoinInfoAddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (config("services.coin_info_address") as $coinInfoAddress) {
            CoinInfoAddress::firstOrCreate($coinInfoAddress);
        }
    }
}
