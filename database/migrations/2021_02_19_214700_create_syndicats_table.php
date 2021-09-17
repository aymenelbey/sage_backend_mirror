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
            $table->string("nomCourt")->nullable();
            $table->string("denominationLegale",300)->nullable();
            $table->string("serin",100)->nullable();
            $table->string("adresse")->nullable();
            $table->string("lat")->nullable();
            $table->string("lang")->nullable();
            $table->string("siteInternet",300)->nullable();
            $table->string("telephoneStandard",200)->nullable();
            $table->string("nombreHabitant")->nullable();
            $table->timestamp("date_enter")->nullable();
            $table->string("logo")->nullable();
            $table->string("GEDRapport",200)->nullable();
            $table->string("email")->nullable();
            $table->string("sinoe")->nullable();
            $table->foreignId('amobe')->nullable()->references('id_enemuration')->on('enemurations');
            $table->foreignId('nature_juridique')->nullable()->references('id_enemuration')->on('enemurations');
            $table->foreignId('departement_siege')->nullable()->references('id_departement')->on('departements');
            $table->foreignId('region_siege')->nullable()->references('id_region')->on('regions')->nullable();
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