<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class AdminFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Admin::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "email"=>$this->faker->unique()->safeEmail,
            "nom"=>$this->faker->firstName,
            "prenom"=>$this->faker->lastName,
            'phone'=>$this->faker->phoneNumber,
            'id_user'=>function() {
                $password=Str::random(10);
                return User::create([
                            'username' => $this->faker->unique()->userName,
                            'init_password' => $password,
                            'password' => Hash::make($password),
                            'typeuser' => "Admin"
                        ])
                        ->id;
            }
        ];
    }
}