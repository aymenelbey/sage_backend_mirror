<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Syndicat;

class SyndicatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Syndicat::factory()
            ->count(100)
            ->create();
    }
}