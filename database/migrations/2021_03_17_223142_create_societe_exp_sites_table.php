<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSocieteExpSitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('societe_exp_sites', function (Blueprint $table) {
            $table->bigIncrements("id_societe_exp_site");
            $table->enum("typeExploitant",["Commune","Epic","Syndicat","Societe"]);
            $table->foreignId('id_site')->references('id_site')->on('sites');
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
        Schema::dropIfExists('societe_exp_sites');
    }
}