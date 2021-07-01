<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShareSite;

class ShareSiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ShareSite::factory()
            ->count(10000)
            ->create();
    }
}