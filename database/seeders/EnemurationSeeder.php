<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\Models\Enemuration;

class EnemurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
            DB::table('enemurations')->insert([
                [
                    "code" => "01A",
                    "value_enum" => "Collecte OMR",
                    "key_enum" => "competence_dechet"
                ],
                [
                    "code" => "01B",
                    "value_enum" => "Collecte sélective",
                    "key_enum" => "competence_dechet"
                ],
                [
                    "code" => "01C",
                    "value_enum" => "Déchèterie",
                    "key_enum" => "competence_dechet"
                ],
                [
                    "code" => "01D",
                    "value_enum" => "Traitement",
                    "key_enum" => "competence_dechet"
                ],
                [
                    "code" => "01E",
                    "value_enum" => "Etude",
                    "key_enum" => "competence_dechet"
                ],
                [
                    "code" => "01f",
                    "value_enum" => "Réhabilitation de décharges",
                    "key_enum" => "competence_dechet"
                ]
            ]);
            Enemuration::factory()
            ->count(500)
            ->create();
    }
}