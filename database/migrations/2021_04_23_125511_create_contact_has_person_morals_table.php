<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactHasPersonMoralsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contact_has_person_morals', function (Blueprint $table) {
            $table->bigIncrements("id_contact_has_person_morals");
            $table->bigInteger('idPersonMoral');
            $table->enum('typePersonMoral',['Syndicat','Epic','Commune','Societe']);
            $table->foreignId('function')->nullable()->references('id_enemuration')->on('enemurations');
            $table->foreignId('id_contact')->references('id_contact')->on('contacts');
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
        Schema::dropIfExists('contact_has_person_morals');
    }
}