<?php

namespace App\Jobs;

use App\Models\Collectivite;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Imports\CollectionsImport;
use App\Exports\CollectionsExport;
use App\Models\Enemuration;
use App\Models\Region;
use App\Models\Departement;
use App\Models\SocieteExploitant;
use App\Events\UserNotification;
use App\Notifications\DataImportsNotif;
use Throwable;
use Excel;
use function MongoDB\BSON\toJSON;

class ImportCompanies implements ShouldQueue
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
                    
                $code_ape=Enemuration::where('key_enum','codeape')
                    ->where('value_enum',$item['code_ape'])
                    ->first();

                $groupes = explode(',', $item['groupe']);
                $groupes = Enemuration::where('key_enum','groupeList')->whereIn('value_enum', $groupes)->get();

                if($nature && $code_ape  && sizeof($groupes) > 0){
                    $adresse= $item['adresse'];
                    SocieteExploitant::create([
                        "denomination"=>$item['denomination'],
                        "groupe"=> array_map(function($groupe){
                            return $groupe['id_enemuration'];
                        }, $groupes->toArray()),
                        "sinoe"=>$item['sinoe'],
                        "serin"=>$item['siren'],
                        "siret"=>$item['siret'],
                        "codeape"=> $code_ape->id_enemuration,
                        "adresse"=>$adresse,
                        "city" => $item['ville'],
                        "postcode" => $item['code_postal'],
                        "effectifs"=>$item['effectifs'],
                        "telephoneStandrad"=>$item['tel_standard'],
                        "siteInternet"=>$item['site_internet'],
                        // "date_enter"=>date(($item['anneeeffectifsunitelegale']?$item['anneeeffectifsunitelegale']:now()->format('Y')).'-01-01'),
                        'nature_juridique'=>$nature->id_enemuration,
                        "country"=>"France",
                    ]);
                }else{
                    if(!$nature){
                        $ignoredData[]=$item + ['Problem trouvé' => 'Nature inexistante'];
                    }
                    if(!$code_ape){
                        $ignoredData[]=$item + ['Problem trouvé' => 'Code APE inexistant'];
                    }

                    if(sizeof($groupes) < 0){
                        $ignoredData[]=$item + ['Problem trouvé' => 'Groupe inexistant'];
                    }
                }
            }else{
                $ignoredData []=$item;
            }
        }
        $filename="exports/Exploitants/".md5("exploitants_exports".time());
        $fileResult=Excel::store(new CollectionsExport($ignoredData), $filename.".xlsx");
        $this->user->notify(new DataImportsNotif([
            'title'=>'La liste des sociétés privées a été importée avec succès.',
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
            'title'=>"Erreur lors de l'importation des Sociétés ",
            'description'=>'subDescData',
            'logo'=>'/media/svg/icons/Costum/WarningReqeust.svg',
            'action'=>'/client/communities/communes',
        ]));
        // broadcast(new UserNotification([
        //     'async'=>true
        // ],$this->user->user_channel));
    }
}