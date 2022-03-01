<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Imports\CollectionsImport;
use App\Exports\CollectionsExport;
use App\Models\Enemuration;
use App\Events\UserNotification;
use App\Notifications\DataImportsNotif;
use App\Models\DataTechnTMB;
use App\Models\User;
use App\Models\GestionnaireHasSite;
use App\Models\SocieteExpSite;
use App\Models\ClientHasSite;
use App\Models\SocieteExploitant;
use App\Models\Syndicat;
use App\Models\DataTechn;
use App\Models\Site;
use App\Models\Region;
use App\Models\Departement;
use App\Http\Helpers\ToolHelper;
use App\Constants\Constants;
use Throwable;
use Excel;

class ImportSitesTMB implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $siteCategorie="TMB";
    protected $filepath;
    protected $user;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($filepath,$user)
    {
        $this->filepath=$filepath;
        $this->user=$user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $dataImport = Excel::toArray(new CollectionsImport, storage_path('app/'.$this->filepath))[0];
        $ignoredData=[];
        $this->setUpEnums($dataImport,'autres_activites_sur_site','autreActivite');
        $this->setUpEnums($dataImport,'type_dinstallations','typeInstallation');
        $this->setUpEnums($dataImport,'types_de_dechets_acceptes','typeDechetAccepter');
        $this->setUpEnums($dataImport,'technologie','technologie');
        $this->setUpEnums($dataImport,'valorisation_energetique_methanisation','valorisationEnergitique');
        foreach($dataImport as $item){
            if(Enemuration::where('key_enum','mode_gestion')->where('value_enum',$item['mode_de_gestion'])->first()){
                /**************** */
                $activity=Enemuration::where('key_enum','autreActivite')->where('value_enum',$item['autres_activites_sur_site'])->first();
                if($activity) $activity=$activity->id_enemuration;
                /**************** */
                $instalation=Enemuration::where('key_enum','typeInstallation')->where('value_enum',$item['type_dinstallations'])->first();
                if($instalation) $instalation=$instalation->id_enemuration;
                /********* */
                $accepter=Enemuration::where('key_enum','typeDechetAccepter')->where('value_enum',$item['types_de_dechets_acceptes'])->first();
                if($accepter) $accepter=$accepter->id_enemuration;
                /********* */
                $technologie=Enemuration::where('key_enum','technologie')->where('value_enum',$item['technologie'])->first();
                if($technologie) $technologie=$technologie->id_enemuration;
                /********* */
                $valorisation=Enemuration::where('key_enum','valorisationEnergitique')->where('value_enum',$item['valorisation_energetique_methanisation'])->first();
                if($valorisation) $valorisation=$valorisation->id_enemuration;
                /********* */
                $region=Region::where('region_code',$item['region'])
                ->orWhere('name_region',$item['region'])
                ->first();
                $depart=Departement::where('departement_code',$item['departement'])
                ->orWhere('name_departement',$item['departement'])
                ->first();
                $societe=SocieteExploitant::where('sinoe',$item['sinoe_exploitant'])
                ->first();
                $syndicat=Syndicat::where('sinoe',$item['sinoe_syndicat'])
                ->first();
                $gestionaire=User::where('username',$item['employe'])
                ->first();
                if($region && $depart && $societe && $syndicat && $gestionaire){
                    $adress= $item['adresse'].',France';
                    $result = ToolHelper::fetchAdress($adress);
                    if(!$result->isEmpty()){
                        $result=$result->first();
                        $coordinates=$result->getCoordinates();

                        $site = Site::where('sinoe', $item['sinoe'])->first();

                        if($site){
                            echo 'Site Found';
                            $dataTech = DataTechn::where('id_site', $site->id_site)->first();
                            if(!$dataTech){
                                $ignoredData []=$item+[
                                    'problème trouvé'=>'Data Technique endomagé'
                                ];
                                continue;
                            }
                            
                            $dataTech = DataTechnTMB::find($dataTech->id_data_tech);

                            if(!$dataTech){
                                $ignoredData []=$item+[
                                    'problème trouvé'=>'Details Data Technique endomagé'
                                ];
                                continue;
                            }

                            $dataTech = $dataTech->update([
                                "typeInstallation"=>$instalation,
                                "typeDechetAccepter"=>$accepter,
                                "technologie"=>$technologie,
                                "quantiteRefus"=>$item['quantite_de_refus_t'],
                                "CSRProduit"=>$item['csr_produit_t_exutoire'],
                                "envoiPreparation"=>$item['envoi_pour_preparation_csr_t'],
                                "tonnageAnnuel"=>$item['tonnage_annuel_2018'],
                                "capaciteNominal"=>$item['capacite_nominale'],
                                "autreActivite"=>$activity,
                                "dernierConstruct"=>$item['constructeur'],
                                "valorisationEnergitique"=>$valorisation
                            ]);

                        }else{
                            $site=Site::create([
                                'denomination'=>$item['denomination'],
                                "categorieSite"=>$this->siteCategorie,
                                "adresse"=>$adress,
                                "latitude"=>$coordinates->getLatitude(),
                                "langititude"=>$coordinates->getLongitude(),
                                "telephoneStandrad"=>$item['telephone'],
                                "anneeCreation"=>$item['annee'],
                                "modeGestion"=>Constants::VALID_MODE[$item['mode_de_gestion']],
                                "sinoe"=>$item['sinoe'],
                                "departement_siege"=>$depart->id_departement,
                                "region_siege"=>$region->id_region
                            ]);
                            $geshassite =  GestionnaireHasSite::create([
                                'id_admin'=>1,
                                'id_gestionnaire'=>$gestionaire->userType->id_gestionnaire,
                                'id_site'=>$site->id_site
                            ]);
                            $clienthassite = ClientHasSite::create([
                                "id_site"=>$site->id_site,
                                "id_collectivite"=>$syndicat->id_collectivite
                            ]);
                            $societe = SocieteExpSite::create([
                                "typeExploitant"=>"Societe",
                                "id_client"=>$societe->id_societe_exploitant,
                                "id_site"=>$site->id_site
                            ]);
                            $dataTech=DataTechnTMB::create([
                                "typeInstallation"=>$instalation,
                                "typeDechetAccepter"=>$accepter,
                                "technologie"=>$technologie,
                                "quantiteRefus"=>$item['quantite_de_refus_t'],
                                "CSRProduit"=>$item['csr_produit_t_exutoire'],
                                "envoiPreparation"=>$item['envoi_pour_preparation_csr_t'],
                                "tonnageAnnuel"=>$item['tonnage_annuel_2018'],
                                "capaciteNominal"=>$item['capacite_nominale'],
                                "autreActivite"=>$activity,
                                "dernierConstruct"=>$item['constructeur'],
                                "valorisationEnergitique"=>$valorisation
                            ]);
                            DataTechn::create([
                                "id_site"=>$site->id_site,
                                "typesite"=>$this->siteCategorie,
                                "id_data_tech"=>$dataTech->id_data_tmb
                            ]);
                        }
                        
                    }else{
                        $ignoredData []=$item+[
                            'problème trouvé'=>'Adress invalid'
                        ];
                    }
                }else{
                    $item['problème trouvé']='';
                    if(!$region){
                        $item['problème trouvé'].='Region invalid , ';
                    }
                    if(!$depart){
                        $item['problème trouvé'].='Departement invalid , ';
                    }
                    if(!$societe){
                        $item['problème trouvé'].='Expolitant invalid , ';
                    }
                    if(!$syndicat){
                        $item['problème trouvé'].='Syndicat sinoe invalid , ';
                    }
                    if(!$gestionaire){
                        $item['problème trouvé'].='Gestionaire invalid , ';
                    }
                    $ignoredData []=$item;
                }
            }else{
                $ignoredData []=$item+[
                    'problème trouvé'=>'Mode de gestion invalid'
                ];
            }
        }
        $filename="exports/Sites/".md5("sites_exports".time());
        $fileResult=Excel::store(new CollectionsExport($ignoredData), $filename.".xlsx");
        $this->user->notify(new DataImportsNotif([
            'title'=>'La list des Site TMB importé avec succès',
            'description'=>'subDescData',
            'logo'=>'/media/svg/icons/Costum/ImportSuccess.svg',
            'action'=>env('APP_HOTS_URL')."imports/download/".str_replace('/','_',$filename),
        ]));
        // broadcast(new UserNotification([
        //     'async'=>true
        // ],$this->user->user_channel));
    }
    private function setUpEnums($data,$keyData,$keyEnum){
        $items=array_unique(array_column($data,$keyData));
        foreach($items as $item){
            if($item){
                if(!Enemuration::where('key_enum',$keyEnum)->where('value_enum',$item)->first()){
                    Enemuration::create([
                        'key_enum'=>$keyEnum,
                        'value_enum'=>$item
                    ]);
                }
            }
        }
    }
    public function failed(Throwable $exception)
    {
        $this->user->notify(new DataImportsNotif([
            'title'=>"Erreur lors de l'importation des sites TMB",
            'description'=>'subDescData',
            'logo'=>'/media/svg/icons/Costum/WarningReqeust.svg',
            'action'=>'/sites',
        ]));
        // broadcast(new UserNotification([
        //     'async'=>true
        // ],$this->user->user_channel));
    }
}