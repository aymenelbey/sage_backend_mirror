<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifySomeFieldsToDataTechnISDNDSTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('data_techn_isdnds', function (Blueprint $table) {
            $table->string("capaciteNominale")->nullable()->change();
            $table->string("capaciteRestante")->nullable()->change();
            $table->string("capaciteReglementaire")->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('data_techn_i_s_d_n_d_s', function (Blueprint $table) {
            //
        });
    }
}