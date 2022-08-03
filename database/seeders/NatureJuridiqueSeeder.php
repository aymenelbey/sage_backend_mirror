<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NatureJuridiqueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(){
        $row = 1;
        if (($handle = fopen(__DIR__."/natures.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                 DB::table('enemurations')->insert([
                    'key_enum' => trim(rtrim($data[0])), 
                    'code' => trim(rtrim($data[1])),
                    'value_enum' => trim(rtrim($data[2]))
                ]);
            }
            fclose($handle);
        }
      
    }
}
