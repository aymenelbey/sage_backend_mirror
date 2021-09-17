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
                ->orWhere('region_code',intval($item['nom_de_la_region']))
                ->orWhere('name_region',$item['nom_de_la_region'])
                ->first();
                $depart=Departement::where('departement_code',$item['code_du_departement'])
                ->orWhere('departement_code',intval($item['code_du_departement']))
                ->orWhere('name_departement',$item['code_du_departement'])
                ->first();
                if($nature && $depart && $region){
                    $client = Collectivite::create([
                        "typeCollectivite"=>"Syndicat"
                    ]);
                    $adresse="";
                    if($item['complementadresseetablissement']){
                        $adresse.=$item['complementadresseetablissement']." - ";
                    }
                    if($item['numerovoieetablissement']){
                        $adresse.=$item['numerovoieetablissement']." - ";
                    }
                    if($item['indicerepetitionetablissement']){
                        $adresse.=$item['indicerepetitionetablissement']." - ";
                    }
                    if($item['typevoieetablissement']){
                        $adresse.=$item['typevoieetablissement']." - ";
                    }
                    if($item['libellevoieetablissement']){
                        $adresse.=$item['libellevoieetablissement']." - ";
                    }
                    Syndicat::create([
                        "nomCourt"=>$item['nom_court'],
                        "denominationLegale"=>$item['denomination_legale'],
                        "serin"=>$item['siret'],
                        "adresse"=>$adresse,
                        "siteInternet"=>$item['site_web'],
                        "telephoneStandard"=>$item['telephone'],
                        "nombreHabitant"=>$item['trancheeffectifsunitelegale'],
                        "date_enter"=>date(($item['anneeeffectifsunitelegale']?$item['anneeeffectifsunitelegale']:now()->format('Y')).'-01-01'),
                        'nature_juridique'=>$nature->id_enemuration,
                        'departement_siege'=>$depart->id_departement,
                        'region_siege'=>$region->id_region,
                        "email"=>$item['mail'],
                        "sinoe"=>$item['code_sinoe'],
                        "country"=>"France",
                        "postcode"=>$item['codepostaletablissement'],
                        "id_collectivite"=>$client->id_collectivite
                    ]);
                }else{
                    $ignoredData []=$item;
                }
            }else{
                $ignoredData []=$item;
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
        broadcast(new UserNotification([
            'async'=>true
        ],$this->user->user_channel));
    }
    public function failed(Throwable $exception)
    {
        $this->user->notify(new DataImportsNotif([
            'title'=>"Erreur lors de l'importation des syndicats",
            'description'=>'subDescData',
            'logo'=>'/media/svg/icons/Costum/WarningReqeust.svg',
            'action'=>'/client/communities/communes',
        ]));
        broadcast(new UserNotification([
            'async'=>true
        ],$this->user->user_channel));
    }
}