<?php

namespace Database\Factories;

use App\Models\ShareSite;
use App\Models\Departement;
use App\Models\Region;
use App\Models\Site;
use App\Models\Admin;
use App\Models\UserPremieum;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShareSiteFactory extends Factory
{
    
    const BASE_SITE=["denomination","categorieSite","adresse","latitude","langititude","siteIntrnet","telephoneStandrad","anneeCreation","photoSite","modeGestion","perdiocitRelance"];
    const DATA_TECH_TMB=["quantiteRefus","CSRProduit","envoiPreparation","tonnageAnnuel","capaciteNominal","dernierConstruct","typeInstallation","typeDechetAccepter","technologie","valorisationEnergitique","autreActivite"];
    const DATA_TECH_TRI=["capaciteHoraire","capaciteNominale","capaciteReglementaire","dateExtension","miseEnService","dernierConstructeur","extension"];
    const DATA_TECH_UVE=['nombreFours',"capacite","nombreChaudiere","debitEau","miseEnService","typeFoursChaudiere","capaciteMaxAnu","videFour","reseauChaleur","rsCommentaire","tonnageReglementaireAp","performenceEnergetique","cycleVapeur","terboalternateur","venteProduction","typeDechetRecus","traitementFumee","installationComplementair","voiTraiFemuee","traitementNOX","equipeProcessTF","reactif","typeTerboalternateur","constructeurInstallation"];
    const DATA_TECH_ISDND=["capaciteNominale","capaciteRestante","capaciteReglementaire","projetExtension","dateExtension","dateOuverture","dateFermeture","dateFermeturePrev"];
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ShareSite::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $site=$this->faker->randomElement(Site::all());
        $depart=$this->faker->randomElement(Departement::all());
        $region=$this->faker->randomElement(Region::all());
        $entry=[
            'Site'=>$site->id_site,
            'Departement'=>$depart->id_departement,
            'Region'=>$region->id_region
        ];
        $type=$this->faker->randomElement(['Site','Departement','Region']);
        $listCheck=constant("self::DATA_TECH_".$site->categorieSite);
        $nbCln=$this->faker->numberBetween(4, count($listCheck));
        $basicWord=$this->faker->randomElements(self::BASE_SITE,$this->faker->numberBetween(4, 11));
        $techWords=$this->faker->randomElements($listCheck,$nbCln);
        $techWords+=$basicWord;
        $clmnShare="";
        foreach($techWords as $word){
            $clmnShare.=$word.'|';
        }
        return [
            'start'=>$this->faker->dateTimeBetween('-10 days', '+3 days')->format('Y-m-d'),
            'end'=>$this->faker->dateTimeBetween('-5 days', '+20 days')->format('Y-m-d'),
            'columns'=>$clmnShare,
            'id_user_premieum'=>$this->faker->randomElement(UserPremieum::all()->pluck('id_user_premieum')),
            'id_data_share'=>$entry[$type],
            'type_data_share'=>$type,
            'id_admin'=>$this->faker->randomElement(Admin::all()->pluck('id_admin')),
            'is_blocked'=>$this->faker->boolean(70)
        ];
    }
}