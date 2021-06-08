<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEPICSTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('epics', function (Blueprint $table) {
            $table->bigIncrements("id_epic");
            $table->string("nomCommun",200);
            $table->string("serin",100);
            $table->string("adresse",200)->nullable();
            $table->string("lat")->nullable();
            $table->string("lang")->nullable();
            $table->string("siteInternet",200)->nullable();
            $table->string("telephoneStandard")->nullable();
            $table->integer("nombreHabitant")->nullable();
            $table->string("logo")->nullable();
            $table->string("nom_court")->nullable();
            $table->string("sinoe")->nullable();
            $table->foreignId('nature_juridique')->references('id_enemuration')->on('enemurations');
            $table->foreignId('departement_siege')->references('id_departement')->on('departements');
            $table->foreignId('region_siege')->references('id_region')->on('regions');
            $table->foreignId('id_collectivite')->references('id_collectivite')->on('collectivites');
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
        Schema::dropIfExists('e_p_i_c_s');
    }
}