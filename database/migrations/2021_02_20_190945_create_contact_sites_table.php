<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactSitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contact_sites', function (Blueprint $table) {
            $table->bigIncrements("id_contact_site");
            $table->foreignId('id_site')->references('id_site')->on('sites');
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
        Schema::dropIfExists('contact_sites');
    }
}