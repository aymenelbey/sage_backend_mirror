<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MultipleGroupeSocietes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
            Schema::table('societe_exploitants', function (Blueprint $table) {
                DB::transaction(function () {
                    DB::statement('ALTER TABLE societe_exploitants DROP CONSTRAINT IF EXISTS "societe_exploitants_groupe_foreign"');
                    DB::statement('ALTER TABLE societe_exploitants ALTER groupe TYPE TEXT;');
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
