<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->bigIncrements("id_contact");
            $table->boolean("status");
            $table->enum("genre",["MME","MR"]);
            $table->string("nom",100);
            $table->string("prenom",100);
            $table->string("telephone1",200)->nullable();
            $table->string("telephone2",200)->nullable();
            $table->string("mobile1",200)->nullable();
            $table->string("mobile2",200)->nullable();
            $table->string("email",200)->nullable();
            $table->string("informations",200)->nullable();
            $table->string("address",100)->nullable();
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
        Schema::dropIfExists('contacts');
    }
}