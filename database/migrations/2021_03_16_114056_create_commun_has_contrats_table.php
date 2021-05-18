<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommunHasContratsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('commun_has_contrats', function (Blueprint $table) {
            $table->bigIncrements("id_commun_has_contrat");
            $table->foreignId('id_contrat')->references('id_contrat')->on('contrats');
            $table->foreignId('id_commune')->references('id_commune')->on('communes');
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
        Schema::dropIfExists('commun_has_contrats');
    }
}