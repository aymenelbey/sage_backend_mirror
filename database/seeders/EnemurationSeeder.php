<?php

namespace Database\Seeders;

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
        Enemuration::factory()
            ->count(150)
            ->create();
    }
}