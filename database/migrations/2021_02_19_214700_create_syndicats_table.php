<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSyndicatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('syndicats', function (Blueprint $table) {
            $table->bigIncrements("id_syndicat");
            $table->string("nomCourt");
            $table->string("denominationLegale",300);
            $table->string("serin",100);
            $table->string("adresse")->nullable();
            $table->string("lat")->nullable();
            $table->string("lang")->nullable();
            $table->string("siteInternet",300)->nullable();
            $table->string("telephoneStandard",200)->nullable();
            $table->integer("nombreHabitant")->nullable();
            $table->timestamp("date_enter")->nullable();
            $table->string("logo")->nullable();
            $table->string("GEDRapport",200)->nullable();
            $table->string("email")->nullable();
            $table->string("sinoe")->nullable();
            $table->foreignId('amobe')->references('id_enemuration')->on('enemurations');
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
        Schema::dropIfExists('syndicats');
    }
}
