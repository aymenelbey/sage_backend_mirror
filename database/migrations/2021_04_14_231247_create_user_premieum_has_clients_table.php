<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPremieumHasClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_premieum_has_clients', function (Blueprint $table) {
            $table->id("id_user_has_client");
            $table->enum("typeClient",["Syndicat","Commune","Epic","Societe"])->nullable();
            $table->integer("id_client");
            $table->foreignId('id_user_premieum')->references('id_user_premieum')->on('user_premieums');
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
        Schema::dropIfExists('user_premieum_has_clients');
    }
}