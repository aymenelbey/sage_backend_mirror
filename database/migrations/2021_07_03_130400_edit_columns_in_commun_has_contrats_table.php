<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EditColumnsInCommunHasContratsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('commun_has_contrats', function (Blueprint $table) {
            $table->dropColumn('id_commune');
            $table->bigInteger('idPersonMoral')->nullable();
            $table->enum('typePersonMoral',['Syndicat','Epic','Commune'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('commun_has_contrats', function (Blueprint $table) {
            //
        });
    }
}