<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGestionnairesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gestionnaires', function (Blueprint $table) {
            $table->bigIncrements("id_gestionnaire");
            $table->string('email')->unique()->nullable();
            $table->string("nom",100);
            $table->string("prenom",100);
            $table->boolean("status");
            $table->enum("genre",["MME","MR"]);
            $table->string("telephone1",200)->nullable();
            $table->string("telephone2",200)->nullable();
            $table->string("mobile1",200)->nullable();
            $table->string("mobile2",200)->nullable();
            $table->foreignId('contract')->nullable()->references('id_enemuration')->on('enemurations');
            $table->foreignId('id_user')->references('id')->on('users');
            $table->foreignId('id_admin')->references('id_admin')->on('admins');
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
        Schema::dropIfExists('gestionnaires');
    }
}