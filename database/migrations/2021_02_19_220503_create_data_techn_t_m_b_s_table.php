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
            $table->string("quantiteRefus")->unsigned()->nullable();
            $table->string("CSRProduit")->unsigned()->nullable();
            $table->string("envoiPreparation")->unsigned()->nullable();
            $table->string("tonnageAnnuel")->unsigned()->nullable();
            $table->string("capaciteNominal")->unsigned()->nullable();
            $table->string("dernierConstruct",300)->nullable();
            /******* */
            $table->foreignId('typeInstallation')->nullable()->references('id_enemuration')->on('enemurations');
            $table->foreignId('typeDechetAccepter')->nullable()->references('id_enemuration')->on('enemurations');
            $table->foreignId('technologie')->nullable()->references('id_enemuration')->on('enemurations');
            $table->foreignId('valorisationEnergitique')->nullable()->references('id_enemuration')->on('enemurations');
            $table->foreignId('autreActivite')->nullable()->references('id_enemuration')->on('enemurations');
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