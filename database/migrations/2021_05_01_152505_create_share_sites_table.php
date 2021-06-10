<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShareSitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('share_sites', function (Blueprint $table) {
            $table->id("id_share_site");
            $table->date('start');
            $table->date('end');
            $table->text('columns');
            $table->foreignId('id_user_premieum')->nullable()->references('id_user_premieum')->on('user_premieums');
            $table->integer('id_data_share');
            $table->string('type_data_share');
            $table->foreignId('id_admin')->nullable()->references('id_admin')->on('admins');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('share_sites');
    }
}