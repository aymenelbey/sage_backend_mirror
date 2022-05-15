<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGedFilesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ged_files', function (Blueprint $table) {
            $table->id();

            $table->text('name');
            $table->text('date');

            $table->bigInteger('category');
            $table->foreign('category')->references('id_enemuration')->on('enemurations');

            
            $table->enum('type', ['syndicats', 'sites', 'epics', 'societies', 'communes']);
            $table->bigInteger('entity_id');
            $table->boolean("shareable")->default(true);
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
        Schema::dropIfExists('ged_files_tables');
    }
}
