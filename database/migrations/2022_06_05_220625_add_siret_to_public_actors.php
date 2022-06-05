<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSiretToPublicActors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('public_actors', function (Blueprint $table) {
            DB::statement('ALTER TABLE communes ADD COLUMN siret varchar(14);');
            DB::statement('ALTER TABLE epics ADD COLUMN siret varchar(14);');
            DB::statement('ALTER TABLE syndicats ADD COLUMN siret varchar(14);');
            DB::statement('ALTER TABLE societe_exploitants ADD COLUMN siret varchar(14);');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('public_actors', function (Blueprint $table) {
            //
        });
    }
}
