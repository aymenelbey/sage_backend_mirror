<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInfoClientHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('info_client_histories', function (Blueprint $table) {
            $table->bigIncrements('id_history');
            $table->integer('id_reference');
            $table->string('prev_value');
            $table->string('referenced_table');
            $table->string('referenced_column');
            $table->timestamp('date_reference');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('info_client_histories');
    }
}
