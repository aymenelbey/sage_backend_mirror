<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDataTechnUVESTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_techn_uves', function (Blueprint $table) {
            $table->bigIncrements("id_data_uve");
            $table->integer('nombreFours')->unsigned()->nullable();
            $table->double("capacite")->nullable();
            $table->integer("nombreChaudiere")->unsigned()->nullable();
            $table->integer("debitEau")->unsigned()->nullable();
            $table->date("miseEnService")->nullable();
            $table->string("typeFoursChaudiere",200)->nullable();
            $table->integer("capaciteMaxAnu")->unsigned()->nullable();
            $table->integer("videFour")->unsigned()->nullable();
            $table->boolean("reseauChaleur")->default(false);
            $table->text("rsCommentaire")->nullable();
            $table->integer("tonnageReglementaireAp")->unsigned()->nullable();
            $table->string("performenceEnergetique",200)->nullable();
            $table->string("cycleVapeur",200)->nullable();
            $table->string("terboalternateur",200)->nullable();
            $table->integer("venteProduction")->unsigned()->nullable();
            /*enumuration */
            $table->foreignId('typeDechetRecus')->references('id_enemuration')->on('enemurations');
            $table->foreignId('traitementFumee')->references('id_enemuration')->on('enemurations');
            $table->foreignId('installationComplementair')->references('id_enemuration')->on('enemurations');
            $table->foreignId('voiTraiFemuee')->references('id_enemuration')->on('enemurations');
            $table->foreignId('traitementNOX')->references('id_enemuration')->on('enemurations');
            $table->foreignId('equipeProcessTF')->references('id_enemuration')->on('enemurations');
            $table->foreignId('reactif')->references('id_enemuration')->on('enemurations');
            $table->foreignId('typeTerboalternateur')->references('id_enemuration')->on('enemurations');
            $table->foreignId('constructeurInstallation')->references('id_enemuration')->on('enemurations');
            /********** */
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
        Schema::dropIfExists('data_techn_u_v_e_s');
    }
}