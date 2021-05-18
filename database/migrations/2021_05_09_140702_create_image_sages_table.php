<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImageSagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('image_sages', function (Blueprint $table) {
            $table->uuid('uid');
            $table->string('name')->nullable();
            $table->string('url')->nullable();
            $table->string('ref_id')->nullable();
            $table->string('status')->nullable();
            $table->primary('uid');
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
        Schema::dropIfExists('image_sages');
    }
}