<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeSiretNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('public_actors', function (Blueprint $table) {
            DB::statement('ALTER TABLE communes ALTER COLUMN siret DROP NOT NULL;');
            DB::statement('ALTER TABLE epics ALTER COLUMN siret DROP NOT NULL;');
            DB::statement('ALTER TABLE syndicats ALTER COLUMN siret DROP NOT NULL;');
            DB::statement('ALTER TABLE societe_exploitants ALTER COLUMN siret DROP NOT NULL;');
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
