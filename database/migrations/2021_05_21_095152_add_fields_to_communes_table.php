<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToCommunesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('communes', function (Blueprint $table) {
            $table->integer("insee")->nullable();
            $table->integer("serin")->nullable();
            $table->foreignId('region_siege')->nullable()->references('id_enemuration')->on('enemurations');
            $table->foreignId('departement_siege')->nullable()->references('id_enemuration')->on('enemurations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('communes', function (Blueprint $table) {
            //
        });
    }
}