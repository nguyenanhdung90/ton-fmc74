<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJettonTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jetton_transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('query_id')->nullable();
            $table->char('trace_id', '44')->unique();
            $table->unsignedBigInteger('lt');
            $table->char('jetton_master', 66);
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
        Schema::dropIfExists('jetton_transfers');
    }
}
