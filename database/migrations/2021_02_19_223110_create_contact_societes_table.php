<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactSocietesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contact_societes', function (Blueprint $table) {
            $table->bigIncrements("id_contact_societe");
            $table->foreignId('id_societe_exploitant')->references('id_societe_exploitant')->on('societe_exploitants');
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
        Schema::dropIfExists('contact_societes');
    }
}