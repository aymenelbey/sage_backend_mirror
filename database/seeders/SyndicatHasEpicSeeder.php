<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SyndicatHasEpic;

class SyndicatHasEpicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SyndicatHasEpic::factory()
            ->count(60)
            ->create();
    }
}