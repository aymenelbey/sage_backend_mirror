<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AttachDepartementToRegion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('departements', function (Blueprint $table) {
            if (!Schema::hasColumn('departements', 'region_code')){
                $table->string('region_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('departements', function (Blueprint $table) {
            if (Schema::hasColumn('departements', 'region_code')){
                $table->dropColumn('region_code');
            }
        });
    }
}
