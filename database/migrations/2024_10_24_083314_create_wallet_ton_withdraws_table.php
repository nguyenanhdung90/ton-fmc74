<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletTonWithdrawsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_ton_withdraws', function (Blueprint $table) {
            $table->id();
            $table->string('to_address_wallet', 66)->nullable();
            $table->string('currency', 20);
            $table->unsignedBigInteger('amount')->default(0);
            $table->unsignedInteger('decimals')->default(0);
            $table->unsignedBigInteger('transaction_id');
            $table->foreign('transaction_id')
                ->references('id')
                ->on('wallet_ton_transactions');
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
        Schema::dropIfExists('wallet_ton_withdraws');
    }
}
