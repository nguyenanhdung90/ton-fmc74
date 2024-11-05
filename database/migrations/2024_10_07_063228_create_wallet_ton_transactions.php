<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletTonTransactions extends Migration
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
            $table->enum('type', ['DEPOSIT', 'WITHDRAW', 'WITHDRAW_EXCESS']);
            $table->string('to_memo', 50)->nullable();
            $table->string('to_address_wallet', 66)->nullable();
            $table->string('hash', '44')->nullable()->index();
            $table->unsignedBigInteger('lt')->nullable();
            $table->string('in_msg_hash', '44')->nullable();
            $table->unsignedBigInteger('amount')->nullable();
            $table->unsignedInteger('decimals')->default(0);
            $table->string('currency', 20);
            $table->bigInteger('occur_ton')->nullable();
            $table->unsignedBigInteger('fixed_fee')->nullable();
            $table->unsignedBigInteger('query_id')->nullable();
            $table->boolean('is_sync_amount')->default(0);
            $table->boolean('is_sync_occur_ton')->default(0);
            $table->boolean('is_sync_fixed_fee')->default(0);
            $table->enum('status', ['INITIATED', 'PROCESSING', 'SUCCESS', 'FAILED'])->default("INITIATED");
            $table->unique(['query_id', 'currency', 'type'], 'query id');
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
