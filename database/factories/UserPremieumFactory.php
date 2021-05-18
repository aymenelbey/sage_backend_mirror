<?php

namespace Database\Factories;

use App\Models\UserPremieum;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserPremieumFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserPremieum::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "email_user_prem"=>$this->faker->unique()->safeEmail,
            "isPaid"=>$this->faker->boolean(50),
            "nom"=>$this->faker->firstName,
            "prenom"=>$this->faker->lastName,
            "lastPaiment"=>$this->faker->date,
            "phone"=>$this->faker->phoneNumber,
            "NbUserCreated"=>0,
            "nbAccess"=>$this->faker->numberBetween(0, 20),
            "created_by"=>$this->faker->randomElement(Admin::all()->pluck('id_admin')),
            'id_user'=>function() {
                $password=Str::random(10);
                return User::create([
                            'username' => $this->faker->unique()->userName,
                            'init_password' => $password,
                            'password' => Hash::make($password),
                            'typeuser' => "UserPremieume"
                        ])
                        ->id;
            },
        ];
    }
}