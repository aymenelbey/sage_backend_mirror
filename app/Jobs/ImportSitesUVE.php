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
use Illuminate\Support\Facades\Log;


class ImportSitesUVE implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $siteCategorie="UVE";
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
            $region = Region::where('region_code',$item['region_du_siege'])->orWhere('name_region',$item['region_du_siege'])->first();
            $exploded_deprt = explode('-', $item['departement_du_siege']);
            
            if(!isset($exploded_deprt[0])){
                $exploded_deprt[0] = '';
            }
            if(!isset($exploded_deprt[1])){
                $exploded_deprt[1] = '';
            }
            
            // Log::info(json_encode($item));
            $depart = Departement::where('departement_code', $exploded_deprt[0])->orWhere('name_departement', $exploded_deprt[1])->first();

            $societe = SocieteExploitant::where('sinoe', $item['code_exploitant_societe_dexploitation_societe'])->first();
            $syndicat = Syndicat::where('sinoe', $item['code_sinoe_syndicat_client_rattache'])->first();
            $gestionaire = User::where('username', $item['gestionaire_username'])->first();

            if($region && $depart && $societe && $syndicat && $gestionaire){
                $adress='';
                
                if($item['adresse']){
                    $adress.=$item['adresse'].' ';
                }
                
                if($item['adresse_suite']){
                    $adress.=$item['adresse_suite'].' ';
                }
                
                if($item['code_postal']){
                    $adress.=$item['code_postal'].' ';
                }

                $adress.=', France';

                $site=Site::create([
                    'denomination'=>$item['denomination'],
                    "categorieSite"=>$this->siteCategorie,
                    "adresse"=> $adress,
                    "latitude"=> $item['geolocalisation_latitude_mercator'],
                    "langititude"=> $item['geolocalisation_longitude_mercator'],
                    "telephoneStandrad"=> $item['telephone'],
                    "anneeCreation"=> $item['annee'],
                    "modeGestion"=> $item['mode_de_gestion'],
                    "sinoe"=> $item['code_sinoe'],
                    "departement_siege" => $depart->id_departement,
                    "region_siege" => $region->id_region
                ]);

                $geshassite =  GestionnaireHasSite::create([
                    'id_admin'=>1,
                    'id_gestionnaire'=>$gestionaire->userType->id_gestionnaire,
                    'id_site'=>$site->id_site
                ]);

                $clienthassite = ClientHasSite::create([
                    "id_site"=> $site->id_site,
                    "id_collectivite"=> $syndicat->id_collectivite
                ]);
                
                $societe = SocieteExpSite::create([
                    "typeExploitant"=>"Societe",
                    "id_client"=>$societe->id_societe_exploitant,
                    "id_site"=>$site->id_site
                ]);

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
        
        }
        $filename="exports/Sites/".md5("sites_exports".time());
        $fileResult=Excel::store(new CollectionsExport($ignoredData), $filename.".xlsx");
        $this->user->notify(new DataImportsNotif([
            'title'=>'La list des Site UVE importé avec succès',
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
            'title'=>"Erreur lors de l'importation des sites TMB",
            'description'=>'subDescData',
            'logo'=>'/media/svg/icons/Costum/WarningReqeust.svg',
            'action'=>'/sites',
        ]));
        broadcast(new UserNotification([
            'async'=>true
        ],$this->user->user_channel));
    }
}