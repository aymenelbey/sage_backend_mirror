<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSharedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shareds', function (Blueprint $table) {
            $table->bigIncrements("id_shared");
            $table->integer("duree");
            $table->foreignId('id_user_premieum')->references('id_user_premieum')->on('user_premieums');
            $table->foreignId('id_admin')->references('id_admin')->on('admins');
            $table->foreignId('id_site')->references('id_site')->on('sites');
            $table->softDeletes();
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
        Schema::dropIfExists('shareds');
    }
}