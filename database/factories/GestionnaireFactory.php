<?php

namespace Database\Factories;

use App\Models\Gestionnaire;
use App\Models\User;
use App\Models\Admin;
use App\Models\Enemuration;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class GestionnaireFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Gestionnaire::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "status"=>$this->faker->boolean(50),
            "genre"=>$this->faker->randomElement(["MME","MR"]),
            "nom"=>$this->faker->firstName,
            "prenom"=>$this->faker->lastName,
            "telephone1"=>$this->faker->phoneNumber,
            "telephone2"=>$this->faker->phoneNumber,
            "mobile1"=>$this->faker->mobileNumber,
            "mobile2"=>$this->faker->mobileNumber,
            "email"=>$this->faker->unique()->safeEmail,
            "contract"=>$this->faker->randomElement(Enemuration::where("keyEnum","contract")->get()->pluck('id_enemuration')),
            'id_user'=>function() {
                $password=Str::random(10);
                return User::create([
                            'username' => $this->faker->unique()->userName,
                            'init_password' => $password,
                            'password' => Hash::make($password),
                            'typeuser' => "Gestionnaire"
                        ])
                        ->id;
            },
            "id_admin"=>$this->faker->randomElement(Admin::all()->pluck('id_admin'))
        ];
    }
}