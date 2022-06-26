<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ConvertDataTechnUvesToJson extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('data_techn_uves', function (Blueprint $table) {
            DB::statement("alter table data_techn_uves alter column infos type json using to_jsonb(infos);");
            DB::statement("alter table data_techn_uves alter column lines type json using to_jsonb(lines);");
            DB::statement("alter table data_techn_uves alter column valorisations type json using to_jsonb(valorisations);");
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
