<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSocieteExpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('societe_exps', function (Blueprint $table) {
            $table->bigIncrements("id_societe_exp");
            $table->foreignId('id_societe_exp_site')->references('id_societe_exp_site')->on('societe_exp_sites');
            $table->foreignId('id_societe_exploitant')->references('id_societe_exploitant')->on('societe_exploitants');
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
        Schema::dropIfExists('societe_exps');
    }
}