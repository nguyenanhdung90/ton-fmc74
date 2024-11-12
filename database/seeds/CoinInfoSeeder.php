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
            if (empty($coinInfo['currency']) || empty($coinInfo['decimals'])) {
                continue;
            }
            if (CoinInfo::where('currency', $coinInfo['currency'])->count()) {
                continue;
            }
            CoinInfo::create($coinInfo);
        }
    }
}
