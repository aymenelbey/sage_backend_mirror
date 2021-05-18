<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateColumnSharedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('column_shareds', function (Blueprint $table) {
            $table->bigIncrements('id_column_shareds');
            $table->string("columnName",50);
            $table->foreignId('id_db_shared')->references('id_db_shared')->on("db_shareds");
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
        Schema::dropIfExists('column_shareds');
    }
}