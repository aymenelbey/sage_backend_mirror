<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShareSite;
use App\Models\Departement;
use App\Models\Region;

class ShareSiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public static function map_to_array($file_content){
        $result = explode("\n", $file_content);
        
        $result_headers = array_map(function($header){
            return rtrim(trim($header));
        }, explode(",", $result[0]));

        $result = array_map(function($line) use($result_headers){
            $line =  explode(",", $line);
            $result = [];
            foreach($line as $index => $cell){
                if(!empty(trim(rtrim($cell)))){
                    $result[$result_headers[$index]] = trim(rtrim($cell));
                }else{
                    $result[$result_headers[$index]] = NULL;
                }
            }
            return $result;
        }, array_slice($result, 1));
        return $result;
    }
    public function run()
    {
        // insert deparements and regions tables
        $departements = ShareSiteSeeder::map_to_array(file_get_contents(__DIR__.'/departments.csv'));
        $regions = ShareSiteSeeder::map_to_array(file_get_contents(__DIR__.'/regions.csv'));

        Departement::insert($departements);
        Region::insert($regions);

        ShareSite::factory()
            ->count(100)
            ->create();
    }
}