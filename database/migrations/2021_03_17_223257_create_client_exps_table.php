<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientExpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_exps', function (Blueprint $table) {
            $table->bigIncrements("id_client_exp");
            $table->foreignId('id_societe_exp_site')->references('id_societe_exp_site')->on('societe_exp_sites');
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
        Schema::dropIfExists('client_exps');
    }
}