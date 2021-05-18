<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCollectivitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('collectivites', function (Blueprint $table) {
            $table->bigIncrements("id_collectivite");
            $table->enum("typeCollectivite",["Commune","EPIC","Syndicat"]);
            $table->foreignId('id_user_premieum')->nullable()->references('id_user_premieum')->on('user_premieums');
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
        Schema::dropIfExists('collectivites');
    }
}