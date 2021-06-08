<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompetanceDechetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('competance_dechets', function (Blueprint $table) {
            $table->bigIncrements('id_competance_dechet');
            $table->string('code',50);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('comment')->nullable();
            $table->integer('owner_competance');
            $table->integer('delegue_competance')->nullable();
            $table->string('delegue_type')->nullable();
            $table->string('owner_type');
            $table->foreignId('competence_dechet')->references('id_enemuration')->on('enemurations');
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
        Schema::dropIfExists('competance_dechets');
    }
}