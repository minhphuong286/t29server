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
        Schema::create('notification_presences', function (Blueprint $table) {
            $table->id();
            $table->string('status')->nullable();
            $table->string('message')->nullable();
            $table->dateTime('read_at')->nullable();
            $table->unsignedBigInteger('presence_room_id')->nullable();
            $table->foreign('presence_room_id')->references('id')->on('presence_rooms');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users');
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
        Schema::dropIfExists('notification_presences');
    }
};
