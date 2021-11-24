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
use App\Models\Collectivite;
use App\Models\Region;
use App\Models\Departement;
use App\Models\EPIC;
use App\Models\Commune;
use App\Events\UserNotification;
use App\Notifications\DataImportsNotif;
use Illuminate\Support\Facades\Http;
use Throwable;
use Excel;
use Log;

class ImportCommunes implements ShouldQueue
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
        $ignoredData=[];
        foreach($dataImport as $item){
            //&& !Commune::where('nomCommune',$item['nom_commune'])->exists()
            if(isset($item['nom_commune'])){
                $region=Region::where('region_code',$item['region_du_siege'])
                ->orWhere('region_code',intval($item['region_du_siege']))
                ->orWhere('name_region',$item['region_du_siege'])
                ->first();
                $depart=Departement::where('departement_code',$item['departement_du_siege'])
                ->orWhere('departement_code',intval($item['departement_du_siege']))
                ->orWhere('name_departement',$item['departement_du_siege'])
                ->first();
                $replaceCC=preg_replace('/CC/', "Communauté de Communes", $item['nom_epci_rattachement'], 1);
                $replaceCA=preg_replace('/CA/', "Communauté d'Agglomération", $item['nom_epci_rattachement'], 1);
                $epic=EPIC::where('nomEpic',$item['nom_epci_rattachement'])
                ->orWhere('nomEpic',$replaceCC)
                ->orWhere('nomEpic',$replaceCA)
                ->orWhere('serin',$item['siren_epci_de_rattachement'])
                ->first();
                if($depart && $region && $epic){
                    $address=[];
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer 41788fb0-c44e-303f-a3eb-12398235b64a'
                    ])->get('https://api.insee.fr/entreprises/sirene/V3/siret', [
                        'q' => "siren:".$item['siret']." AND etablissementSiege:true",
                    ]);
                    if($response->ok()){
                        $data=$response->json();
                        $data=$data['etablissements'][0];
                        if($data){
                            $data=$data['adresseEtablissement'];
                            if($data){
                                $adresse="";
                                if($data['complementAdresseEtablissement']){
                                    $adresse.=$data['complementAdresseEtablissement']." ";
                                }
                                if($data['numeroVoieEtablissement']){
                                    $adresse.=$data['numeroVoieEtablissement']." ";
                                }
                                if($data['indiceRepetitionEtablissement']){
                                    $adresse.=$data['indiceRepetitionEtablissement']." ";
                                }
                                if($data['typeVoieEtablissement']){
                                    $adresse.=$data['typeVoieEtablissement']." ";
                                }
                                if($data['libelleVoieEtablissement']){
                                    $adresse.=$data['libelleVoieEtablissement']." ";
                                }
                                $address=[
                                    "adresse"=>$adresse,
                                    "postcode"=>$data['codePostalEtablissement']
                                ];
                            }
                        }
                    }
                    $client = Collectivite::create([
                        "typeCollectivite"=>"Commune"
                    ]);
                    Commune::create([
                        "nomCommune"=>$item['nom_commune'],
                        "insee"=>$item['insee'],
                        "serin"=>$item['siret'],
                        'departement_siege'=>$depart->id_departement,
                        'region_siege'=>$region->id_region,
                        "nombreHabitant"=>$item['nbr_dhabitants'],
                        "date_enter"=>date(($item['date_habitant']?$item['date_habitant']:now()->format('Y')).'-01-01'),
                        "country"=>$item['pays'],
                        'id_epic'=>$epic->id_epic,
                        "id_collectivite"=>$client->id_collectivite
                    ]+$address);
                }else{
                    $ignoredData []=$item; 
                }
            }else{
                $ignoredData []=$item;
            }
        }
        $filename="exports/Communes/".md5("communes_exports".time());
        $fileResult=Excel::store(new CollectionsExport($ignoredData), $filename.".xlsx");
        $this->user->notify(new DataImportsNotif([
            'title'=>'La list des Communes importé avec succès',
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
            'title'=>"Erreur lors de l'importation des Communes",
            'description'=>'subDescData',
            'logo'=>'/media/svg/icons/Costum/WarningReqeust.svg',
            'action'=>'/client/communities/communes',
        ]));
        broadcast(new UserNotification([
            'async'=>true
        ],$this->user->user_channel));
    }
}