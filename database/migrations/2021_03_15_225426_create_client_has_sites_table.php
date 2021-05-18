<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientHasSitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_has_sites', function (Blueprint $table) {
            $table->bigIncrements("id_client_has_sites");
            $table->foreignId('id_site')->references('id_site')->on('sites');
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
        Schema::dropIfExists('client_has_sites');
    }
}