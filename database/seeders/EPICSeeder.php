<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EPIC;

class EPICSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        EPIC::factory()
            ->count(20)
            ->create();
    }
}