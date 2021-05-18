<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserSimplesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_simples', function (Blueprint $table) {
            $table->bigIncrements("id_user_simple");
            $table->string("nom",100)->nullable();
            $table->string("prenom",100)->nullable();
            $table->string("email_user_sim")->unique()->nullable();
            $table->foreignId('id_user')->references('id')->on('users');
            $table->foreignId('created_by')->references('id')->on('users');
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
        Schema::dropIfExists('user_simples');
    }
}