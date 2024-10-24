<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletTonDeposits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_ton_deposits', function (Blueprint $table) {
            $table->id();
            $table->string('memo', 50)->nullable();
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
        Schema::dropIfExists('wallet_ton_deposits');
    }
};
