<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;


class GedFilesUpdateConstraints extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE ged_files DROP CONSTRAINT IF EXISTS "ged_files_type_check"');
        DB::statement("ALTER TABLE ged_files ADD CONSTRAINT \"ged_files_type_check\" CHECK (type::text = ANY (ARRAY['syndicats'::character varying, 'sites'::character varying, 'epics'::character varying, 'societies'::character varying, 'societe_exploitants'::character varying, 'communes'::character varying]::text[]))");

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
