<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ContactHasPersonMoral;

class ContactHasPersonMoralSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ContactHasPersonMoral::factory()
            ->count(100)
            ->create();
    }
}