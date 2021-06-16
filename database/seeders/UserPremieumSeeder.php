<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserPremieum;

class UserPremieumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        UserPremieum::factory()
            ->count(20)
            ->create();
    }
}