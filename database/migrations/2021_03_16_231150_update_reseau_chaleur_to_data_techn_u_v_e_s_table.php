<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateReseauChaleurToDataTechnUVESTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('data_techn_uves', function (Blueprint $table) {
            $table->boolean("reseauChaleur")->default(false)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('data_techn_u_v_e_s', function (Blueprint $table) {
            //
        });
    }
}