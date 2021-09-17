<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifySomeFieldsToDataTechnUVESTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('data_techn_uves', function (Blueprint $table) {
            $table->string('nombreFours')->nullable()->change();
            $table->string("capacite")->nullable()->change();
            $table->string("nombreChaudiere")->nullable()->change();
            $table->string("debitEau")->nullable()->change();
            $table->string("capaciteMaxAnu")->nullable()->change();
            $table->string("videFour")->nullable()->change();
            $table->string("tonnageReglementaireAp")->nullable()->change();
            $table->string("venteProduction")->nullable()->change();
            
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