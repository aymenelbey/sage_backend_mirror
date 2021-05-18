<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDataTechnTMBSTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_techn_tmbs', function (Blueprint $table) {
            $table->bigIncrements('id_data_tmb');
            $table->integer("quantiteRefus")->unsigned()->nullable();
            $table->integer("CSRProduit")->unsigned()->nullable();
            $table->integer("envoiPreparation")->unsigned()->nullable();
            $table->integer("tonnageAnnuel")->unsigned()->nullable();
            $table->integer("capaciteNominal")->unsigned()->nullable();
            $table->string("dernierConstruct",300)->nullable();
            /******* */
            $table->foreignId('typeInstallation')->references('id_enemuration')->on('enemurations');
            $table->foreignId('typeDechetAccepter')->references('id_enemuration')->on('enemurations');
            $table->foreignId('technologie')->references('id_enemuration')->on('enemurations');
            $table->foreignId('valorisationEnergitique')->references('id_enemuration')->on('enemurations');
            $table->foreignId('autreActivite')->references('id_enemuration')->on('enemurations');
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
        Schema::dropIfExists('data_techn_t_m_b_s');
    }
}