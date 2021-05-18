<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->bigIncrements("id_site");
            $table->string("denomination");
            $table->enum("categorieSite",["UVE","TRI","TMB","ISDND"]);
            $table->string("adresse");
            $table->string("latitude")->nullable();
            $table->string("langititude")->nullable();
            $table->string("siteIntrnet")->nullable();
            $table->string("telephoneStandrad")->nullable();
            $table->string("anneeCreation")->nullable();
            $table->string("photoSite")->nullable();
            $table->enum("modeGestion",["Gestion privée", "Prestation de service", "Regie", "DSP"]);
            $table->string("perdiocitRelance");
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
        Schema::dropIfExists('sites');
    }
}