<?php

namespace Database\Factories;

use App\Models\Syndicat;
use App\Models\Collectivite;
use App\Models\Enemuration;
use App\Models\Departement;
use App\Models\Region;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class SyndicatFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Syndicat::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "nomCourt"=>$this->faker->words(2, true),
            "denominationLegale"=>$this->faker->words(2, true),
            "serin"=>$this->faker->siren,
            "adresse"=>$this->faker->address,
            "lat"=>$this->faker->latitude($min = 44, $max = 49),
            "lang"=>$this->faker->longitude($min = -0.4, $max = 5),
            "siteInternet"=>$this->faker->url,
            "telephoneStandard"=>$this->faker->phoneNumber,
            "nombreHabitant"=>$this->faker->numberBetween(0, 2000),
            'amobe'=>$this->faker->randomElement(Enemuration::where("key_enum","amobe")->get()->pluck('id_enemuration')),
            'nature_juridique'=>$this->faker->randomElement(Enemuration::where("key_enum","nature_juridique")->get()->pluck('id_enemuration')),
            "departement_siege"=>$this->faker->randomElement(Departement::all()->pluck('id_departement')),
            "region_siege"=>$this->faker->randomElement(Region::all()->pluck('id_region')),
            'date_enter'=>Carbon::now(),
            "id_collectivite"=>Collectivite::create([
                    "typeCollectivite"=>"Syndicat"
                ])->id_collectivite
        ];
    }
}