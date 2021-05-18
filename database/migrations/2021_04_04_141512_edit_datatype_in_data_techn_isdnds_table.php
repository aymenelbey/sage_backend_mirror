<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EditDatatypeInDataTechnIsdndsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('data_techn_isdnds', function (Blueprint $table) {
            $table->string("dateExtension")->nullable()->change();
            $table->string("dateOuverture")->nullable()->change();
            $table->string("dateFermeture")->nullable()->change();
            $table->string("dateFermeturePrev")->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('data_techn_isdnds', function (Blueprint $table) {
            //
        });
    }
}