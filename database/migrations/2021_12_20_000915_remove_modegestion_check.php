<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveModegestionCheck extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sites', function (Blueprint $table) {
            DB::transaction(function () {
                DB::statement('ALTER TABLE sites DROP CONSTRAINT IF EXISTS "sites_modeGestion_check";');
                DB::statement('ALTER TABLE sites ALTER "modeGestion" TYPE VARCHAR;');
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
        //
    }
}
