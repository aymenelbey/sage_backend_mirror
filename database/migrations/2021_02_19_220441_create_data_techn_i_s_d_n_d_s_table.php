<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDataTechnISDNDSTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_techn_isdnds', function (Blueprint $table) {
            $table->bigIncrements('id_data_isdnd');
            $table->integer("capaciteNominale")->unsigned()->nullable();
            $table->integer("capaciteRestante")->unsigned()->nullable();
            $table->integer("capaciteReglementaire")->unsigned()->nullable();
            $table->boolean("projetExtension")->default(false);
            $table->date("dateExtension")->nullable();
            $table->date("dateOuverture")->nullable();
            $table->date("dateFermeture")->nullable();
            $table->date("dateFermeturePrev")->nullable();
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
        Schema::dropIfExists('data_techn_i_s_d_n_d_s');
    }
}