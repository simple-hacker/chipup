<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuyInsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buy_ins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('cash_game_id');  // Will need to change to Polymorphism for Tournaments.  Need gameable_id and gameable_type columns instead.
            $table->bigInteger('amount')->default(0);
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
        Schema::dropIfExists('buy_ins');
    }
}
