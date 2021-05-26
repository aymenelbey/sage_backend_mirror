<?php

namespace Database\Factories;

use App\Models\EPIC;
use App\Models\Collectivite;
use App\Models\Enemuration;
use Illuminate\Database\Eloquent\Factories\Factory;

class EPICFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EPIC::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "nomEpic"=>$this->faker->words(2, true),
            "serin"=>$this->faker->siren,
            "adresse"=>$this->faker->address,
            "lat"=>$this->faker->latitude($min = 44, $max = 49),
            "lang"=>$this->faker->longitude($min = -0.4, $max = 5),
            "siteInternet"=>$this->faker->url,
            "telephoneStandard"=>$this->faker->phoneNumber,
            "nombreHabitant"=>$this->faker->numberBetween(0, 2000),
            "nature_juridique"=>$this->faker->randomElement(Enemuration::where("key_enum","nature_juridique")->get()->pluck('id_enemuration')),
            "departement_siege"=>$this->faker->randomElement(Enemuration::where("key_enum","departement_siege")->get()->pluck('id_enemuration')),
            "competence_dechet"=>$this->faker->randomElement(Enemuration::where("key_enum","competence_dechet")->get()->pluck('id_enemuration')),
            "region_siege"=>$this->faker->randomElement(Enemuration::where("key_enum","region_siege")->get()->pluck('id_enemuration')),
            "exerciceCompetance"=>$this->faker->randomElement(["en direct","déléguée"]),
            "id_collectivite"=>Collectivite::create([
                    "typeCollectivite"=>"EPIC"
                ])->id_collectivite
        ];
    }
}