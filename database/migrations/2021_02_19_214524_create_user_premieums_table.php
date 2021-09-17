<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPremieumsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_premieums', function (Blueprint $table) {
            $table->bigIncrements("id_user_premieum");
            $table->string("nom",100)->nullable();
            $table->string("prenom",100)->nullable();
            $table->string("email_user_prem")->unique()->nullable();
            $table->boolean("isPaid");
            $table->datetime("lastPaiment");
            $table->string("nbAccess");
            $table->foreignId('id_user')->references('id')->on('users');
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
        Schema::dropIfExists('user_premieums');
    }
}