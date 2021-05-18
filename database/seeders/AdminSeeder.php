<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Admin::create([
            "id_user"=>1,
            "nom"=>"zino",
            "prenom"=>"nino",
            "email_admin"=>"itachibatna@gmail.com"
        ]);
        Admin::factory()
            ->count(50)
            ->create();
    }
}