<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gestionnaire;

class GestionnaireSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Gestionnaire::factory()
            ->count(20)
            ->create();
    }
}