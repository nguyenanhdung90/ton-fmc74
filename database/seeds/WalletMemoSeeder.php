<?php

use App\Models\WalletMemo;
use Illuminate\Database\Seeder;

class WalletMemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        WalletMemo::firstOrCreate([
            "user_name" => "user_1",
            "memo" => "memo"
        ]);
        WalletMemo::firstOrCreate([
            "user_name" => "user_2",
            "memo" => "memo2"
        ]);
    }
}
