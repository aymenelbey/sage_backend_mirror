<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSyndicatHasEpicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('syndicat_has_epics', function (Blueprint $table) {
            $table->bigIncrements("id_syndicat_has_epic");
            $table->foreignId('id_syndicat')->references('id_syndicat')->on('syndicats');
            $table->foreignId('id_epic')->references('id_epic')->on('epics');
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
        Schema::dropIfExists('syndicat_has_epics');
    }
}