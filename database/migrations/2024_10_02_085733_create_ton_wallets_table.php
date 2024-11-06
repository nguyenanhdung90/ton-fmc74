<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTonWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_ton_memos', function (Blueprint $table) {
            $table->id();
            $table->string('memo', 50);
            $table->char('currency', 6);
            $table->unsignedBigInteger('amount')->default(0);
            $table->unsignedTinyInteger('decimals')->default(0);
            $table->unique(['memo', 'currency'], 'memo_currency');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ton_wallets');
    }
};
