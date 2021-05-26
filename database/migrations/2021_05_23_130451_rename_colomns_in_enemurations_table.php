<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameColomnsInEnemurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('enemurations', function (Blueprint $table) {
            $table->renameColumn("`keyEnum`", 'key_enum');
            $table->renameColumn("`valueEnum`", 'value_enum');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('enemurations', function (Blueprint $table) {
            //
        });
    }
}