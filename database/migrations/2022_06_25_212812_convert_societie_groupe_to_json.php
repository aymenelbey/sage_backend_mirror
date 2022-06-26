<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ConvertSocietieGroupeToJson extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('societe_exploitants', function (Blueprint $table) {
            DB::statement("alter table societe_exploitants alter column groupe type json using to_jsonb(groupe);");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('json', function (Blueprint $table) {
            //
        });
    }
}
