<?php

namespace Database\Factories;

use App\Models\Site;
use App\Models\DataTechn;
use App\Models\GestionnaireHasSite;
use App\Models\Admin;
use App\Models\Collectivite;
use App\Models\SocieteExpSite;
use App\Models\ClientHasSite;
use App\Models\Gestionnaire;
use App\Models\Enemuration;
use App\Models\SocieteExploitant;
use App\Models\EPIC;
use App\Models\Syndicat;
use App\Models\Commune;
use App\Models\Departement;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

class SiteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Site::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "denomination"=>$this->faker->words(3, true),
            "sinoe"=>$this->faker->words(3, true),
            "categorieSite"=>$this->faker->randomElement(['UVE','TRI','TMB','ISDND']),
            "adresse"=>$this->faker->address,
            "latitude"=>$this->faker->unique()->latitude($min = 43.5, $max = 49.5),
            "langititude"=>$this->faker->unique()->longitude($min = -0.4, $max = 5.5),
            "siteIntrnet"=>$this->faker->url,
            "telephoneStandrad"=>$this->faker->phoneNumber,
            "anneeCreation"=>$this->faker->year(),
            "modeGestion"=>$this->faker->randomElement(['Gestion privée','Prestation de service','Regie','DSP']),
            "perdiocitRelance"=>$this->faker->dateTimeBetween('-300 days', '+300 days')->format('m/yy'),
            "departement_siege"=>$this->faker->randomElement(Departement::all()->pluck('id_departement')),
            "region_siege"=>$this->faker->randomElement(Region::all()->pluck('id_region')),
        ];
    }
    public function configure(){
        return $this->afterCreating(function (Site $site) {
            GestionnaireHasSite::create([
                    'id_admin'=>$this->faker->randomElement(Admin::all()->pluck('id_admin')),
                    'id_gestionnaire'=>$this->faker->randomElement(Gestionnaire::all()->pluck('id_gestionnaire')),
                    'id_site'=>$site->id_site
                ]);
            ClientHasSite::create([
                "id_site"=>$site->id_site,
                "id_collectivite"=>$this->faker->randomElement(Collectivite::all()->pluck('id_collectivite'))
            ]);
            $typeExp=$this->faker->randomElement(['Syndicat','Epic','Commune','Societe']);
            switch($typeExp){
                case "Syndicat":
                    $idExpo=$this->faker->randomElement(Syndicat::all()->pluck('id_syndicat'));
                    break;
                case "Epic":
                    $idExpo=$this->faker->randomElement(EPIC::all()->pluck('id_epic'));
                    break;
                case "Commune":
                    $idExpo=$this->faker->randomElement(Commune::all()->pluck('id_commune'));
                    break;
                case "Societe": 
                    $idExpo=$this->faker->randomElement(SocieteExploitant::all()->pluck('id_societe_exploitant'));
                    break;
            }
            SocieteExpSite::create([
                "typeExploitant"=>$typeExp,
                "id_client"=>$idExpo,
                "id_site"=>$site->id_site
            ]);
            $techClassName='App\Models\DataTechn'.$site->categorieSite;
            switch($site->categorieSite){
                case "UVE":
                    $techData=[
                        'nombreFours'=>$this->faker->numberBetween(0, 100),
                        "capacite"=>$this->faker->numberBetween(0, 100),
                        "nombreChaudiere"=>$this->faker->numberBetween(0, 100),
                        "debitEau"=>$this->faker->numberBetween(0, 100),
                        "miseEnService"=>$this->faker->dateTimeBetween('-300 days', '+300 days')->format('d/m/y'),
                        "typeFoursChaudiere"=>$this->faker->numberBetween(0, 100),
                        "capaciteMaxAnu"=>$this->faker->numberBetween(0, 100),
                        "videFour"=>$this->faker->numberBetween(0, 100),
                        "reseauChaleur"=>$this->faker->boolean(50),
                        "rsCommentaire"=>$this->faker->numberBetween(0, 100),
                        "tonnageReglementaireAp"=>$this->faker->numberBetween(0, 100),
                        "performenceEnergetique"=>$this->faker->numberBetween(0, 100),
                        "cycleVapeur"=>$this->faker->numberBetween(0, 100),
                        "terboalternateur"=>$this->faker->numberBetween(0, 100),
                        "venteProduction"=>$this->faker->numberBetween(0, 100),
                        /****** */
                        "typeDechetRecus"=>$this->faker->randomElement(Enemuration::where("key_enum","typeDechetRecus")->get()->pluck('id_enemuration')),
                        "traitementFumee"=>$this->faker->randomElement(Enemuration::where("key_enum","traitementFumee")->get()->pluck('id_enemuration')),
                        "installationComplementair"=>$this->faker->randomElement(Enemuration::where("key_enum","installationComplementair")->get()->pluck('id_enemuration')),
                        "voiTraiFemuee"=>$this->faker->randomElement(Enemuration::where("key_enum","voiTraiFemuee")->get()->pluck('id_enemuration')),
                        "traitementNOX"=>$this->faker->randomElement(Enemuration::where("key_enum","traitementNOX")->get()->pluck('id_enemuration')),
                        "equipeProcessTF"=>$this->faker->randomElement(Enemuration::where("key_enum","equipeProcessTF")->get()->pluck('id_enemuration')),
                        "reactif"=>$this->faker->randomElement(Enemuration::where("key_enum","reactif")->get()->pluck('id_enemuration')),
                        "typeTerboalternateur"=>$this->faker->randomElement(Enemuration::where("key_enum","typeTerboalternateur")->get()->pluck('id_enemuration')),
                        "constructeurInstallation"=>$this->faker->randomElement(Enemuration::where("key_enum","constructeurInstallation")->get()->pluck('id_enemuration'))
                    ];
                    break;
                case "TRI": 
                    $techData=[
                        "capaciteHoraire"=>$this->faker->numberBetween(0, 100),
                        "capaciteNominale"=>$this->faker->numberBetween(0, 100),
                        "capaciteReglementaire"=>$this->faker->numberBetween(0, 100),
                        "dateExtension"=>$this->faker->dateTimeBetween('-300 days', '+300 days')->format('d/m/y'),
                        "miseEnService"=>$this->faker->dateTimeBetween('-300 days', '+300 days')->format('d/m/y'),
                        "dernierConstructeur"=>$this->faker->numberBetween(0, 100),
                        /**** */
                        "extension"=>$this->faker->randomElement(Enemuration::where("key_enum","extension")->get()->pluck('id_enemuration'))
                    ];
                    break;
                case "TMB":
                    $techData=[
                        "quantiteRefus"=>$this->faker->numberBetween(0, 100),
                        "CSRProduit"=>$this->faker->numberBetween(0, 100),
                        "envoiPreparation"=>$this->faker->numberBetween(0, 100),
                        "tonnageAnnuel"=>$this->faker->numberBetween(0, 100),
                        "capaciteNominal"=>$this->faker->numberBetween(0, 100),
                        "dernierConstruct"=>$this->faker->numberBetween(0, 100),
                        /********* */
                        "typeInstallation"=>$this->faker->randomElement(Enemuration::where("key_enum","typeInstallation")->get()->pluck('id_enemuration')),
                        "typeDechetAccepter"=>$this->faker->randomElement(Enemuration::where("key_enum","typeDechetAccepter")->get()->pluck('id_enemuration')),
                        "technologie"=>$this->faker->randomElement(Enemuration::where("key_enum","technologie")->get()->pluck('id_enemuration')),
                        "valorisationEnergitique"=>$this->faker->randomElement(Enemuration::where("key_enum","valorisationEnergitique")->get()->pluck('id_enemuration')),
                        "autreActivite"=>$this->faker->randomElement(Enemuration::where("key_enum","autreActivite")->get()->pluck('id_enemuration'))
                    ];
                    break;
                case "ISDND":
                    $techData=[
                        "capaciteNominale"=>$this->faker->numberBetween(0, 100),
                        "capaciteRestante"=>$this->faker->numberBetween(0, 100),
                        "capaciteReglementaire"=>$this->faker->numberBetween(0, 100),
                        "projetExtension"=>$this->faker->boolean(50),
                        "dateExtension"=>$this->faker->dateTimeBetween('-300 days', '+300 days')->format('d/m/y'),
                        "dateOuverture"=>$this->faker->dateTimeBetween('-300 days', '+300 days')->format('d/m/y'),
                        "dateFermeture"=>$this->faker->dateTimeBetween('-300 days', '+300 days')->format('d/m/y'),
                        "dateFermeturePrev"=>$this->faker->dateTimeBetween('-300 days', '+300 days')->format('d/m/y')
                    ]; 
                    break;
            }
            $dataTech=$techClassName::create($techData);
            $dataTech = DataTechn::create([
                "id_site"=>$site->id_site,
                "typesite"=>$site->categorieSite,
                "id_data_tech"=>$dataTech->{"id_data_".strtolower($site->categorieSite)}
            ]);
        });
    }
}