<?php

namespace Database\Factories;

use App\Models\SyndicatHasEpic;
use App\Models\Syndicat;
use App\Models\EPIC;
use Illuminate\Database\Eloquent\Factories\Factory;

class SyndicatHasEpicFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SyndicatHasEpic::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "id_syndicat"=>$this->faker->randomElement(Syndicat::all()->pluck('id_syndicat')),
            "id_epic"=>$this->faker->randomElement(EPIC::where("exerciceCompetance","déléguée")->get()->pluck('id_epic'))
        ];
    }
}