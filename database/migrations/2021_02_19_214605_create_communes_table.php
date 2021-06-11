<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommunesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('communes', function (Blueprint $table) {
            $table->bigIncrements("id_commune");
            $table->string("nomCommune",200);
            $table->string("adresse")->nullable();
            $table->string("lat")->nullable();
            $table->string("lang")->nullable();
            $table->string("logo")->nullable();
            $table->integer("nombreHabitant");
            $table->date("date_enter")->nullable();
            $table->integer("insee")->nullable();
            $table->integer("serin")->nullable();
            $table->foreignId('region_siege')->nullable()->references('id_region')->on('regions');
            $table->foreignId('departement_siege')->nullable()->references('id_departement')->on('departements');
            $table->foreignId('id_epic')->references('id_epic')->on('epics')->nullable();
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
        Schema::dropIfExists('communes');
    }
}