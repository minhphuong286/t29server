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
        Schema::table('notification_privates', function (Blueprint $table) {
            $table->dateTimeTz('read_at')->nullable()->change();
        });

        Schema::table('notification_presences', function (Blueprint $table) {
            $table->dateTimeTz('read_at')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
