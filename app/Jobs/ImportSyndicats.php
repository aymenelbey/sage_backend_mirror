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
use App\Models\Syndicat;
use App\Models\Collectivite;
use App\Models\Region;
use App\Models\Departement;
use App\Events\UserNotification;
use App\Notifications\DataImportsNotif;
use Throwable;
use Excel;
use Log;

class ImportSyndicats implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $natures=array_unique(array_column($dataImport,'nature_juridique'));
        foreach($natures as $nature){
            if($nature){
                if(!Enemuration::where('key_enum','nature_juridique')->where('value_enum',$nature)->first()){
                    Enemuration::create([
                        'key_enum'=>'nature_juridique',
                        'value_enum'=>$nature
                    ]);
                }
            }
        }
        $ignoredData=[];
        foreach($dataImport as $item){
            if(isset($item['siret'])){
                $nature=Enemuration::where('key_enum','nature_juridique')
                ->where('value_enum',$item['nature_juridique'])
                ->first();
                $region=Region::where('region_code',$item['nom_de_la_region'])
                ->orWhere('region_code',strlen($item['nom_de_la_region']) == 1 ? '0'.$item['nom_de_la_region'] : intval($item['nom_de_la_region']))
                ->orWhere('name_region',$item['nom_de_la_region'])
                ->first();
                $depart=Departement::where('departement_code',$item['code_du_departement'])
                ->orWhere('departement_code', strlen($item['code_du_departement']) == 1 ? '0'.$item['code_du_departement'] : intval($item['code_du_departement']))
                ->orWhere('name_departement',$item['code_du_departement'])
                ->first();
                if($nature && $depart && $region){
                    $syndicat = Syndicat::where('serin', $item['siret'])->first();
                    if($syndicat){
                        $syndicat->update([
                            "nomCourt"=>$item['nom_court'],
                            "denominationLegale"=>$item['denomination_legale'],
                            "serin"=>$item['siret'],
                            "adresse"=> $item['adresse'],
                            "city"=>$item['libelle_commune_etablissement'],
                            "siteInternet"=>$item['site_web'],
                            "telephoneStandard"=>$item['telephone'],
                            "nombreHabitant"=>$item['tranche_effectifs_unite_legale'],
                            "date_enter"=>date(($item['annee_effectifs_unite_legale']?$item['annee_effectifs_unite_legale']:now()->format('Y')).'-01-01'),
                            'nature_juridique'=>$nature->id_enemuration,
                            'departement_siege'=>$depart->id_departement,
                            'region_siege'=>$region->id_region,
                            "email"=>$item['mail'],
                            "sinoe"=>$item['code_sinoe'],
                            "country"=>"France",
                            "postcode"=>$item['code_postal_etablissement']
                        ]);
                    }else{
                        $client = Collectivite::create([
                            "typeCollectivite"=>"Syndicat"
                        ]);
                        Syndicat::create([
                            "nomCourt"=>$item['nom_court'],
                            "denominationLegale"=>$item['denomination_legale'],
                            "serin"=>$item['siret'],
                            "adresse"=> $item['adresse'],
                            "city"=>$item['libelle_commune_etablissement'],
                            "siteInternet"=>$item['site_web'],
                            "telephoneStandard"=>$item['telephone'],
                            "nombreHabitant"=>$item['tranche_effectifs_unite_legale'],
                            "date_enter"=>date(($item['annee_effectifs_unite_legale']?$item['annee_effectifs_unite_legale']:now()->format('Y')).'-01-01'),
                            'nature_juridique'=>$nature->id_enemuration,
                            'departement_siege'=>$depart->id_departement,
                            'region_siege'=>$region->id_region,
                            "email"=>$item['mail'],
                            "sinoe"=>$item['code_sinoe'],
                            "country"=>"France",
                            "postcode"=>$item['code_postal_etablissement'],
                            "id_collectivite"=>$client->id_collectivite
                        ]);
                    }
                    
                }else{
                    $item['Problem trouvé'] = '';
                    if(!$nature){
                        $item['Problem trouvé'] .= 'Nature non existante';
                    }
                    if(!$region){
                        $item['Problem trouvé'] .= ", Region n'existe pas";
                    }
                    if(!$depart){
                        $item['Problem trouvé'] .= ", Departement n'existe pas";
                    }
                    print_r($item);
                    $ignoredData []= $item;
                }
            }else{
                $item['Problem trouvé'] .= ", Siret obligatoire";
                print_r($item);
                $ignoredData []= $item;
            }
        }
        $filename="exports/Syndicats/".md5("syndicats_exports".time());
        $fileResult=Excel::store(new CollectionsExport($ignoredData), $filename.".xlsx");
        $this->user->notify(new DataImportsNotif([
            'title'=>'La list des syndicats importé avec succès',
            'description'=>'subDescData',
            'logo'=>'/media/svg/icons/Costum/ImportSuccess.svg',
            'action'=>env('APP_HOTS_URL')."imports/download/".str_replace('/','_',$filename)
        ]));
        // broadcast(new UserNotification([
        //     'async'=>true
        // ],$this->user->user_channel));
    }
    public function failed(Throwable $exception)
    {
        $this->user->notify(new DataImportsNotif([
            'title'=>"Erreur lors de l'importation des syndicats",
            'description'=>'subDescData',
            'logo'=>'/media/svg/icons/Costum/WarningReqeust.svg',
            'action'=>'/client/communities/communes',
        ]));
        // broadcast(new UserNotification([
        //     'async'=>true
        // ],$this->user->user_channel));
    }
}