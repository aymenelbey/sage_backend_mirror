<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactCollectivitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contact_collectivites', function (Blueprint $table) {
            $table->bigIncrements("id_contact_collectivite");
            $table->foreignId('id_collectivite')->references('id_collectivite')->on('collectivites');
            $table->foreignId('id_contact')->references('id_contact')->on('contacts');
            $table->foreignId('function')->nullable()->references('id_enemuration')->on('enemurations');
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
        Schema::dropIfExists('contact_collectivites');
    }
}