<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletMemosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_memos', function (Blueprint $table) {
            $table->id();
            $table->string('memo', 50)->unique();
            $table->string('user_name', 191)->unique();

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
        Schema::dropIfExists('wallet_memos');
    }
};
