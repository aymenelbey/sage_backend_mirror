<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->id('id_region');
            $table->string('region_code',10)->nullable();
            $table->string("name_region");
             $table->string("slug_region");
            $table->softDeletes();
            $table->timestamps();
        });
        DB::insert("INSERT INTO regions VALUES (1,'01','Guadeloupe','guadeloupe'),(2,'02','Martinique','martinique'),(3,'03','Guyane','guyane'),(4,'04','La Réunion','la reunion'),(5,'06','Mayotte','mayotte'),(6,'11','Île-de-France','ile de france'),(7,'24','Centre-Val de Loire','centre val de loire'),(8,'27','Bourgogne-Franche-Comté','bourgogne franche comte'),(9,'28','Normandie','normandie'),(10,'32','Hauts-de-France','hauts de france'),(11,'44','Grand Est','grand est'),(12,'52','Pays de la Loire','pays de la loire'),(13,'53','Bretagne','bretagne'),(14,'75','Nouvelle-Aquitaine','nouvelle aquitaine'),(15,'76','Occitanie','occitanie'),(16,'84','Auvergne-Rhône-Alpes','auvergne rhone alpes'),(17,'93','Provence-Alpes-Côte d''Azur','provence alpes cote dazur'),(18,'94','Corse','corse'),(19,'COM','Collectivités d''Outre-Mer','collectivites doutre mer')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('regions');
    }
}