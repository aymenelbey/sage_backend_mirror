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
            $table->string('nombreFours')->unsigned()->nullable();
            $table->double("capacite")->nullable();
            $table->string("nombreChaudiere")->unsigned()->nullable();
            $table->string("debitEau")->unsigned()->nullable();
            $table->date("miseEnService")->nullable();
            $table->string("typeFoursChaudiere",200)->nullable();
            $table->string("capaciteMaxAnu")->unsigned()->nullable();
            $table->string("videFour")->unsigned()->nullable();
            $table->boolean("reseauChaleur")->default(false);
            $table->text("rsCommentaire")->nullable();
            $table->string("tonnageReglementaireAp")->unsigned()->nullable();
            $table->string("performenceEnergetique",200)->nullable();
            $table->string("cycleVapeur",200)->nullable();
            $table->string("terboalternateur",200)->nullable();
            $table->string("venteProduction")->unsigned()->nullable();
            /*enumuration */
            $table->foreignId('typeDechetRecus')->nullable()->references('id_enemuration')->on('enemurations');
            $table->foreignId('traitementFumee')->nullable()->references('id_enemuration')->on('enemurations');
            $table->foreignId('installationComplementair')->nullable()->references('id_enemuration')->on('enemurations');
            $table->foreignId('voiTraiFemuee')->nullable()->references('id_enemuration')->on('enemurations');
            $table->foreignId('traitementNOX')->nullable()->references('id_enemuration')->on('enemurations');
            $table->foreignId('equipeProcessTF')->nullable()->references('id_enemuration')->on('enemurations');
            $table->foreignId('reactif')->nullable()->references('id_enemuration')->on('enemurations');
            $table->foreignId('typeTerboalternateur')->nullable()->references('id_enemuration')->on('enemurations');
            $table->foreignId('constructeurInstallation')->nullable()->references('id_enemuration')->on('enemurations');
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