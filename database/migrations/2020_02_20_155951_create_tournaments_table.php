<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTournamentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tournaments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('start_time')->useCurrent();
            $table->timestamp('end_time')->nullable();
            $table->bigInteger('profit')->default(0);
            $table->bigInteger('buy_in');
            $table->unsignedBigInteger('limit_id')->nullable();
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->string('name')->nullable();
            $table->unsignedInteger('entries')->nullable();
            $table->unsignedInteger('position')->nullable();
            $table->string('location')->nullable();
            $table->text('comments')->nullable();
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
        Schema::dropIfExists('tournaments');
    }
}
