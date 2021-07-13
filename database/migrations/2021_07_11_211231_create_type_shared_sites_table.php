<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypeSharedSitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('type_shared_sites', function (Blueprint $table) {
            $table->id("id_type_shared_site");
            $table->string("site_categorie")->nullable();
            $table->foreignId('id_share_site')->references('id_share_site')->on('share_sites');
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
        Schema::dropIfExists('type_shared_sites');
    }
}