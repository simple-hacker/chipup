<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashOutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_outs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->morphs('game');
            $table->string('currency', 6);
            $table->bigInteger('amount')->default(0);
            $table->bigInteger('locale_amount')->default(0);
            $table->bigInteger('session_locale_amount')->default(0);
            $table->timestamps();

            $table->unique(['game_id', 'game_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cash_outs');
    }
}
