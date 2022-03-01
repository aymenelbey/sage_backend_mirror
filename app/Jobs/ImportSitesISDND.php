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
use App\Events\UserNotification;
use App\Notifications\DataImportsNotif;
use App\Models\DataTechnISDND;
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

class ImportSitesISDND implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $siteCategorie="ISDND";
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
        foreach($dataImport as $item){
            if(Enemuration::where('key_enum','mode_gestion')->where('value_enum',$item['mode_de_gestion'])->first()){
                $region=Region::where('region_code',$item['nom_de_la_region'])
                ->orWhere('name_region',$item['nom_de_la_region'])
                ->first();
                $depart=Departement::where('departement_code',$item['code_du_departement'])
                ->orWhere('name_departement',$item['code_du_departement'])
                ->first();
                $societe=SocieteExploitant::where('sinoe',$item['sinoe_expolitant'])
                ->first();
                $syndicat=Syndicat::where('sinoe',$item['sinoe_syndicat'])
                ->first();
                $gestionaire=User::where('username',$item['employe'])
                ->first();
                if($region && $depart && $societe && $syndicat && $gestionaire){
                    $adress = $item['adresse'].',France';

                    $result = ToolHelper::fetchAdress($adress);
                    if(!$result->isEmpty()){
                        $result=$result->first();
                        $coordinates=$result->getCoordinates();
                        
                        $site = Site::where('sinoe', $item['sinoe'])->first();

                        if($site){
                                                        
                            $dataTech = DataTechn::where('id_site', $site->id_site)->first();
                            if(!$dataTech){
                                $ignoredData []=$item+[
                                    'problème trouvé'=>'Data Technique endomagé'
                                ];
                                continue;
                            }
                            
                            $dataTech = DataTechnISDND::find($dataTech->id_data_tech);

                            if(!$dataTech){
                                $ignoredData []=$item+[
                                    'problème trouvé'=>'Details Data Technique endomagé'
                                ];
                                continue;
                            }

                            $dataTech = $dataTech->update([
                                "capaciteNominale"=>$item['capacite_nominale'],
                                "capaciteRestante"=>$item['capacite_restante'],
                                "capaciteReglementaire"=>$item['capacite_reglementaire'],
                                "projetExtension"=>strtolower($item['projet_dextension'])=='oui',
                                "dateExtension"=>$item['date_dextension'],
                                "dateOuverture"=>$item['date_douverture'],
                                "dateFermeture"=>$item['date_de_fermeture'],
                                "dateFermeturePrev"=>$item['date_de_fermeture_previsionnelle']
                            ]);

                        }else{
                            $site = Site::create([
                                "denomination"=>$item['denomination'],
                                "categorieSite"=>$this->siteCategorie,
                                "adresse"=>$adress,
                                "latitude"=>$coordinates->getLatitude(),
                                "langititude"=>$coordinates->getLongitude(),
                                "telephoneStandrad"=>$item['telephone'],
                                "anneeCreation"=>$item['annee_creation'],
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

                            $dataTech=DataTechnISDND::create([
                                "capaciteNominale"=>$item['capacite_nominale'],
                                "capaciteRestante"=>$item['capacite_restante'],
                                "capaciteReglementaire"=>$item['capacite_reglementaire'],
                                "projetExtension"=>strtolower($item['projet_dextension'])=='oui',
                                "dateExtension"=>$item['date_dextension'],
                                "dateOuverture"=>$item['date_douverture'],
                                "dateFermeture"=>$item['date_de_fermeture'],
                                "dateFermeturePrev"=>$item['date_de_fermeture_previsionnelle']
                            ]);
    
                            DataTechn::create([
                                "id_site"=>$site->id_site,
                                "typesite"=>$this->siteCategorie,
                                "id_data_tech"=>$dataTech->id_data_isdnd
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
            'title'=>'La list des Site ISDND importé avec succès',
            'description'=>'subDescData',
            'logo'=>'/media/svg/icons/Costum/ImportSuccess.svg',
            'action'=>env('APP_HOTS_URL')."imports/download/".str_replace('/','_',$filename),
        ]));
        broadcast(new UserNotification([
            'async'=>true
        ],$this->user->user_channel));
    }
    public function failed(Throwable $exception)
    {
        $this->user->notify(new DataImportsNotif([
            'title'=>"Erreur lors de l'importation des sites ISDND",
            'description'=>'subDescData',
            'logo'=>'/media/svg/icons/Costum/WarningReqeust.svg',
            'action'=>'/sites',
        ]));
        broadcast(new UserNotification([
            'async'=>true
        ],$this->user->user_channel));
    }
}