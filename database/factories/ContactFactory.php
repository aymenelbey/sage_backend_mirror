<?php

namespace Database\Factories;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Contact::class;

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
            "informations"=>$this->faker->words(5, true),
            'address'=>$this->faker->address
        ];
    }
}