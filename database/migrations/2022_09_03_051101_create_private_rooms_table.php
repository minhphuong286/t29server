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
        Schema::create('private_rooms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_one')->nullable();
            $table->unsignedBigInteger('user_two')->nullable();
            $table->foreign('user_one')->references('id')->on('users');
            $table->foreign('user_two')->references('id')->on('users');
            $table->string('status');
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
        Schema::dropIfExists('private_rooms');
    }
};
