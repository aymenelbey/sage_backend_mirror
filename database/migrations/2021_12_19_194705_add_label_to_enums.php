<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLabelToEnums extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('enemurations', function (Blueprint $table) {
            DB::transaction(function () {
                DB::statement('ALTER TABLE enemurations ADD COLUMN code TEXT DEFAULT NULL;');
            }); 
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
            DB::transaction(function () {
                DB::statement('ALTER TABLE enemurations DROP COLUMN code;');
            }); 
        });
    }
}
