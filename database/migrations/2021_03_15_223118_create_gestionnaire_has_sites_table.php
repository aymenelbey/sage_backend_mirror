<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGestionnaireHasSitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gestionnaire_has_sites', function (Blueprint $table) {
            $table->bigIncrements('id_gestionnaire_has_sites');
            $table->foreignId('id_admin')->references('id_admin')->on('admins');
            $table->foreignId('id_gestionnaire')->references('id_gestionnaire')->on('gestionnaires');
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
        Schema::dropIfExists('gestionnaire_has_sites');
    }
}