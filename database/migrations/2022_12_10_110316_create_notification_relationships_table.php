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
        Schema::create('notification_relationships', function (Blueprint $table) {
            $table->id();
            $table->string('status')->nullable();
            $table->string('message',500)->nullable();
            $table->unsignedBigInteger('relationship_id')->nullable();
            $table->foreign('relationship_id')->references('id')->on('user_relationships');
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
        Schema::dropIfExists('notification_relationships');
    }
};
