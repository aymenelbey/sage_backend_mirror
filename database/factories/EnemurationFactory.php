<?php

namespace Database\Factories;

use App\Models\Enemuration;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnemurationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Enemuration::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'keyEnum' => $this->faker->randomElement(["nature_juridique","region_siege","departement_siege","competence_dechet","amobe","codeape","function_person","contract","typeDechetRecus","traitementFumee","installationComplementair","voiTraiFemuee","traitementNOX","equipeProcessTF","reactif","typeTerboalternateur","constructeurInstallation","extension","typeInstallation","typeDechetAccepter","technologie","valorisationEnergitique","autreActivite"]),
            'valueEnum' => $this->faker->name
        ];
    }
}