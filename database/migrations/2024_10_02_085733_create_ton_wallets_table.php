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
            $table->string('currency', 20);
            $table->unsignedBigInteger('amount')->default(0);
            $table->unsignedInteger('decimals')->default(0);
            $table->unique('memo', 'currency');
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
