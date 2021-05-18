<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UserSeeder::class,
            EnemurationSeeder::class,
            SocieteExploitantSeeder::class,
            AdminSeeder::class,
            GestionnaireSeeder::class,
            UserPremieumSeeder::class,
            SyndicatSeeder::class,
            EPICSeeder::class,
            SyndicatHasEpicSeeder::class,
            CommuneSeeder::class,
            ContactSeeder::class,
            ContactHasPersonMoralSeeder::class,
            SiteSeeder::class,
            ShareSiteSeeder::class
        ]);
    }
}