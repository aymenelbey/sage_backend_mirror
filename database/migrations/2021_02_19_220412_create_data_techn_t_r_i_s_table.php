<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDataTechnTRISTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_techn_tris', function (Blueprint $table) {
            $table->bigIncrements('id_data_tri');
            $table->integer("capaciteHoraire")->unsigned()->nullable();
            $table->integer("capaciteNominale")->unsigned()->nullable();
            $table->integer("capaciteReglementaire")->unsigned()->nullable();
            $table->date("dateExtension")->nullable();
            $table->date("miseEnService")->nullable();
            $table->string("dernierConstructeur",400)->nullable();
            /******* */
            $table->foreignId('extension')->references('id_enemuration')->on('enemurations');
            /*********** */
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
        Schema::dropIfExists('data_techn_t_r_i_s');
    }
}