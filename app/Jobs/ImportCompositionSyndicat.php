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
use App\Models\EPIC;
use App\Models\Collectivite;
use App\Models\Region;
use App\Models\Departement;
use App\Events\UserNotification;
use App\Notifications\DataImportsNotif;
use App\Models\CompetanceDechet;
use Throwable;
use Excel;
use Log;

class ImportCompositionSyndicat implements ShouldQueue
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
        $competances=array_unique(array_column($dataImport,'competence'));
        foreach($competances as $competance){
            if($competance){
                if(!Enemuration::where('key_enum','competence_dechet')->where('value_enum',$competance)->first()){
                    Enemuration::create([
                        'key_enum'=>'competence_dechet',
                        'value_enum'=>$competance
                    ]);
                }
            }
        }
        foreach($dataImport as $item){
            if($item['competence']){
                $epci=EPIC::where('sinoe',$item['code_sinoe_adherant'])
                ->orWhere('nomEpic',$item['nom_adherant'])
                ->first();
                $competance=Enemuration::where('key_enum','competence_dechet')
                ->where('value_enum',$item['competence'])
                ->first();
                if($epci && $competance){
                    if(strtolower($item['ouiexerceedeleguee'])=='oui'){
                        CompetanceDechet::create([
                            'code'=>"N/A",
                            'start_date'=>now()->format('Y-m-d'),
                            'end_date'=>now()->format('Y-m-d'),
                            'owner_competance'=>$epci->id_epic,
                            'owner_type'=>"EPIC",
                            'competence_dechet'=>$competance->id_enemuration
                        ]);
                    }else{
                        $syndicat=Syndicat::where('sinoe',$item['code_sinoe_syndcat'])
                        ->orWhere('nomCourt',$item['nom_syndicat_a_qui_la_competence_est_deleguee'])
                        ->first();
                        if($syndicat){
                            CompetanceDechet::create([
                                'code'=>"N/A",
                                'start_date'=>now()->format('Y-m-d'),
                                'end_date'=>now()->format('Y-m-d'),
                                'owner_competance'=>$epci->id_epic,
                                'owner_type'=>"EPIC",
                                'competence_dechet'=>$competance->id_enemuration,
                                'delegue_competance'=>$syndicat->id_syndicat,
                                'delegue_type'=>"Syndicat"
                            ]);
                        }else{
                            $ignoredData []=$item;
                        }
                    }
                }else{
                    $ignoredData []=$item;
                }
            }else{
                $ignoredData []=$item; 
            }
            
        }
        $filename="exports/Compositions/".md5("composition_exports".time());
        $fileResult=Excel::store(new CollectionsExport($ignoredData), $filename.".xlsx");
        $this->user->notify(new DataImportsNotif([
            'title'=>'La list des Competances importé avec succès',
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
            'title'=>"Erreur lors de l'importation des Competances",
            'description'=>'subDescData',
            'logo'=>'/media/svg/icons/Costum/WarningReqeust.svg',
            'action'=>'/client/communities/epic',
        ]));
        broadcast(new UserNotification([
            'async'=>true
        ],$this->user->user_channel));
    }
}