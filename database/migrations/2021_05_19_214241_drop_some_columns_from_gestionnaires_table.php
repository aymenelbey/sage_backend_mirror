<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropSomeColumnsFromGestionnairesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gestionnaires', function (Blueprint $table) {
            $table->dropColumn("telephone2");
            $table->dropColumn("mobile2");
            $table->dropColumn("contract");
            $table->renameColumn('telephone1', 'telephone');
            $table->renameColumn('mobile1', 'mobile');
            $table->enum("societe",["Sage_engineering","Sage_expert","Sage_industry"])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gestionnaires', function (Blueprint $table) {
            //
        });
    }
}