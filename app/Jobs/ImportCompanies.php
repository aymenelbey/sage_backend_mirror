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
use App\Models\Region;
use App\Models\Departement;
use App\Models\SocieteExploitant;
use App\Events\UserNotification;
use App\Notifications\DataImportsNotif;
use Throwable;
use Excel;

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
        $natures=array_unique(array_column($dataImport,'categorie_juridqiue_lib'));
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
        $code_apes=array_unique(array_column($dataImport,'lib_code_ape'));
        foreach($code_apes as $code_ape){
            if($code_ape){
                if(!Enemuration::where('key_enum','codeape')->where('value_enum',$code_ape)->first()){
                    Enemuration::create([
                        'key_enum'=>'codeape',
                        'value_enum'=>$code_ape
                    ]);
                }
            }
        }
        $groupes=array_unique(array_column($dataImport,'groupe'));
        foreach($groupes as $groupe){
            if($groupe){
                if(!Enemuration::where('key_enum','groupeList')->where('value_enum',$groupe)->first()){
                    Enemuration::create([
                        'key_enum'=>'groupeList',
                        'value_enum'=>$groupe
                    ]);
                }
            }
        }
        $ignoredData=[];
        foreach($dataImport as $item){
            if($item['denomination']){
                $nature=Enemuration::where('key_enum','nature_juridique')
                ->where('value_enum',$item['categorie_juridqiue_lib'])
                ->first();
                $codeape=Enemuration::where('key_enum','codeape')
                ->where('value_enum',$item['lib_code_ape'])
                ->first();
                $groupe=Enemuration::where('key_enum','groupeList')
                ->where('value_enum',$item['groupe'])
                ->first();
                SocieteExploitant::create([
                    "groupe"=>$groupe ? $groupe->id_enemuration:null,
                    "denomination"=>$item['denomination'],
                    "serin"=>$item['siret'],
                    "codeape"=>$codeape?$codeape->id_enemuration:null,
                    "adresse"=>$item['adresse'],
                    "telephoneStandrad"=>$item['telephone'],
                    "effectifs"=>$item['effectif'],
                    "date_enter"=>date(($item['anne_effcetif']?$item['anne_effcetif']:now()->format('Y')).'-01-01'),
                    'nature_juridique'=>$nature?$nature->id_enemuration:null,
                    "city"=>$item['ville'],
                    "sinoe"=>$item['sinoe'],
                    "postcode"=>$item['code_postal'],
                ]); 
            }else{
                $ignoredData []=$item;
            }
        }
        $filename="exports/Companies/".md5("companies_exports".time());
        $fileResult=Excel::store(new CollectionsExport($ignoredData), $filename.".xlsx");
        $this->user->notify(new DataImportsNotif([
            'title'=>'La list des Sociétés importé avec succès',
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
            'title'=>"Erreur lors de l'importation des Sociétés",
            'description'=>'subDescData',
            'logo'=>'/media/svg/icons/Costum/WarningReqeust.svg',
            'action'=>'/client/communities/communes',
        ]));
        broadcast(new UserNotification([
            'async'=>true
        ],$this->user->user_channel));
    }
}