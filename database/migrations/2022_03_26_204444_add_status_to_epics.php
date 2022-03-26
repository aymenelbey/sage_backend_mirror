<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToEpics extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('epics', function (Blueprint $table) {
            $table->enum('status', ['VALIDATED', 'NOT_VALIDATED', 'NOT_PUBLISHED'])->default('NOT_VALIDATED');
            $table->foreignId('updated_by')->nullable()->references('id_admin')->on('admins');
            $table->foreignId('status_updated_by')->nullable()->references('id_admin')->on('admins');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('epics', function (Blueprint $table) {
            //
        });
    }
}
