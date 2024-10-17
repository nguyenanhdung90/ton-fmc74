<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_ton_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('from_address_wallet', 66)->nullable();
            $table->string('from_memo', 50)->nullable();
            $table->enum('type', ['DEPOSIT', 'WITHDRAW']);
            $table->string('to_memo', 50)->nullable();
            $table->string('hash', '44')->index();
            $table->unsignedDecimal('amount', 20, 9)->default(0);
            $table->string('currency', 20);
            $table->unsignedDecimal('total_fees', 20, 9)->default(0)->comment('currency = TON');
            $table->unsignedBigInteger('lt');
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
        Schema::dropIfExists('wallet_ton_transactions');
    }
};
