<?php

namespace Database\Factories;

use App\Models\ContactHasPersonMoral;
use App\Models\Enemuration;
use App\Models\PersonFunction;
use App\Models\Contact;
use App\Models\Syndicat;
use App\Models\EPIC;
use App\Models\Commune;
use App\Models\SocieteExploitant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactHasPersonMoralFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ContactHasPersonMoral::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $typePerson=$this->faker->randomElement(['Syndicat','Epic','Commune','Societe']);
        switch($typePerson){
            case "Syndicat":
                $idPerson=$this->faker->randomElement(Syndicat::all()->pluck('id_syndicat'));
                break;
            case "Epic":
                $idPerson=$this->faker->randomElement(EPIC::all()->pluck('id_epic'));
                break;
            case "Commune":
                $idPerson=$this->faker->randomElement(Commune::all()->pluck('id_commune'));
                break;
            case "Societe": 
                $idPerson=$this->faker->randomElement(SocieteExploitant::all()->pluck('id_societe_exploitant'));
                break;
        };
        return [
            "idPersonMoral"=>$idPerson,
            "id_contact"=>$this->faker->unique()->randomElement(Contact::all()->pluck('id_contact')),
            "typePersonMoral"=>$typePerson
        ];
    }
    public function configure(){
        return $this->afterCreating(function (ContactHasPersonMoral $person_moral) {
            PersonFunction::factory()
            ->count($this->faker->numberBetween(1, 4))
            ->create([
                "functionPerson"=>$this->faker->randomElement(Enemuration::where("key_enum","function_person")->get()->pluck('id_enemuration')),
                "id_person"=>$person_moral->id_contact_has_person_morals
            ]);
        });
    }
}