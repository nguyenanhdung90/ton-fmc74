<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->string('user_name', 191);
            $table->unsignedBigInteger('amount')->default(0);
            $table->char('currency', 20);
            $table->unsignedTinyInteger('decimals')->default(0);
            $table->boolean("is_active")->default(true);

            $table->unique(['user_name', 'currency'], 'user_name_currency');
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
        Schema::dropIfExists('wallets');
    }
}
