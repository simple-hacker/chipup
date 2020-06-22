<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            // $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->boolean('setup_complete')->default(false);
            $table->string('locale', 30)->default('en-GB');
            $table->string('currency', 6)->default('GBP');
            $table->bigInteger('bankroll')->default(0);
            $table->unsignedBigInteger('default_stake_id')->nullable();
            $table->unsignedBigInteger('default_limit_id')->nullable();
            $table->unsignedBigInteger('default_variant_id')->nullable();
            $table->unsignedBigInteger('default_table_size_id')->nullable();
            $table->string('default_location')->nullable();
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
        Schema::dropIfExists('users');
    }
}
