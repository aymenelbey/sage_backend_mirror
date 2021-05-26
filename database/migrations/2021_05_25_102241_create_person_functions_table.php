<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonFunctionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('person_functions', function (Blueprint $table) {
            $table->id('id_person_function');
            $table->foreignId('functionPerson')->nullable()->references('id_enemuration')->on('enemurations');
            $table->foreignId('id_person')->nullable()->references('id_contact_has_person_morals')->on('contact_has_person_morals');
            $table->boolean("status")->default(true);
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
        Schema::dropIfExists('person_functions');
    }
}