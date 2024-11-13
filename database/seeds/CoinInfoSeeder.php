<?php

use App\Models\CoinInfo;
use Illuminate\Database\Seeder;

class CoinInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (config("services.coin_infos") as $coinInfo) {
            CoinInfo::firstOrCreate($coinInfo);
        }
    }
}
