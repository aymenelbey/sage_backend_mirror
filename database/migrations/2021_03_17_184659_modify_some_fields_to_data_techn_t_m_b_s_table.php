<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifySomeFieldsToDataTechnTMBSTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('data_techn_tmbs', function (Blueprint $table) {
            $table->string("quantiteRefus")->nullable()->change();
            $table->string("CSRProduit")->nullable()->change();
            $table->string("envoiPreparation")->nullable()->change();
            $table->string("tonnageAnnuel")->nullable()->change();
            $table->string("capaciteNominal")->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('data_techn_t_m_b_s', function (Blueprint $table) {
            //
        });
    }
}