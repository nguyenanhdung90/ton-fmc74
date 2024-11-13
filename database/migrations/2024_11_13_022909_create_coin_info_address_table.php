<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoinInfoAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coin_info_address', function (Blueprint $table) {
            $table->id();
            $table->char('currency', 20);
            $table->char('hex_master_address', 66)->unique();
            $table->enum('environment', ["MAIN", "TEST"]);
            $table->foreign('currency')
                ->references('currency')
                ->on('coin_infos');
            $table->unique(['currency', 'environment'], 'currency_environment');
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
        Schema::dropIfExists('coin_info_address');
    }
}
