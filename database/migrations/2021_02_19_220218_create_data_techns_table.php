<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDataTechnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_techns', function (Blueprint $table) {
            $table->bigIncrements("id_data_techn");
            $table->foreignId('id_site')->references('id_site')->on('sites');
            $table->bigInteger('id_data_tech');
            $table->enum("typesite",["UVE","TRI","TMB","ISDND"])->default("UVE");
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
        Schema::dropIfExists('data_techns');
    }
}