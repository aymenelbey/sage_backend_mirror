<?php

namespace Database\Factories;

use App\Models\Commune;
use App\Models\EPIC;
use App\Models\Enemuration;
use App\Models\Departement;
use App\Models\Region;
use App\Models\Collectivite;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommuneFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Commune::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "nomCommune"=>$this->faker->region,
            "adresse"=>$this->faker->address,
            "serin"=>$this->faker->randomNumber(9, true),
            "insee"=>$this->faker->randomNumber(5, true),
            "lat"=>$this->faker->latitude($min = 44, $max = 49),
            "lang"=>$this->faker->longitude($min = -0.4, $max = 5),
            "nombreHabitant"=>$this->faker->numberBetween(0, 2000),
            "departement_siege"=>$this->faker->randomElement(Departement::all()->pluck('id_departement')),
            "region_siege"=>$this->faker->randomElement(Region::all()->pluck('id_region')),
            'id_epic'=>$this->faker->randomElement(EPIC::all()->pluck('id_epic')),
            'id_collectivite'=>Collectivite::create([
                    "typeCollectivite"=>"Commune"
                ])->id_collectivite,
        ];
    }
}