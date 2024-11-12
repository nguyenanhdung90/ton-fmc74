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
        Schema::create('wallet_ton_address', function (Blueprint $table) {
            $table->id();
            $table->string('memo', 50);
            $table->string('user_name');
            $table->foreign('user_name')
                ->references('user_name')
                ->on('wallets');
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
        Schema::dropIfExists('wallet_ton_address');
    }
};
