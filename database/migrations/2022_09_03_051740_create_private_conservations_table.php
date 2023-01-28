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
        Schema::create('private_conservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('message')->nullable();
            $table->unsignedBigInteger('private_room_id')->nullable();
            $table->string('status')->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('private_room_id')->references('id')->on('private_rooms');
            $table->timestamps();
        });

        Schema::table('private_rooms', function (Blueprint $table) {
            $table->string('status')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('private_conservations');
    }
};
