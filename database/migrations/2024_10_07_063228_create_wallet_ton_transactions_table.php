<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletTonTransactionsTable extends Migration
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
            $table->char('from_address_wallet', 66)->nullable();
            $table->string('from_memo', 50)->nullable();
            $table->enum('type', ['DEPOSIT', 'WITHDRAW', 'WITHDRAW_EXCESS']);
            $table->string('to_memo', 50)->nullable();
            $table->char('to_address_wallet', 66)->nullable();
            $table->char('hash', '44')->nullable()->index();
            $table->unsignedBigInteger('lt')->nullable();
            $table->char('in_msg_hash', '44')->nullable();
            $table->unsignedBigInteger('amount')->nullable();
            $table->unsignedTinyInteger('decimals')->default(0);
            $table->char('currency', 6);
            $table->bigInteger('occur_ton')->nullable();
            $table->unsignedInteger('fixed_fee')->nullable();
            $table->unsignedBigInteger('query_id')->nullable();
            $table->boolean('is_sync_amount')->default(0);
            $table->boolean('is_sync_occur_ton')->default(0);
            $table->boolean('is_sync_fixed_fee')->default(0);
            $table->enum('status', ['INITIATED', 'PROCESSING', 'SUCCESS', 'FAILED'])->default("INITIATED");
            $table->unique(['query_id', 'type'], 'query id');
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
