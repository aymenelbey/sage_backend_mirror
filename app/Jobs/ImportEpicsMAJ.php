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
use App\Models\Collectivite;
use App\Models\Region;
use App\Models\Departement;
use App\Models\EPIC;
use Carbon\Carbon;

use App\Models\InfoClientHistory;

use App\Events\UserNotification;
use App\Notifications\DataImportsNotif;
use Throwable;
use Excel;


class ImportEpicsMAJ implements ShouldQueue
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
        $ignoredData = [];

        foreach($dataImport as $epic){

            $dbEpic = EPIC::where('serin', $epic['siret'])->first();


            if(!$dbEpic || $dbEpic->nombreHabitant == $epic['effectif']){
                // append to file
                $ignoredData[] = $epic;
                continue;
            }
            
            InfoClientHistory::customCreate([
                'id_reference' => $dbEpic->id_epic,
                'referenced_table' => "Epic",
                'referenced_column' => 'nombreHabitant',
                'date_reference' => $dbEpic->date_enter,
                'prev_value' => $dbEpic->nombreHabitant
            ]);
            
            if(!$dbEpic->update(['nombreHabitant' => $epic['effectif'], 'date_enter'=> Carbon::now()])){
                $ignoredData[] = $epic;
            }
        }


        $filename="exports/EPCI/".md5("epcis_exports_maj".time());
        $fileResult=Excel::store(new CollectionsExport($ignoredData), $filename.".xlsx");

        $this->user->notify(new DataImportsNotif([
            'title'=>'La list des EPCI non mise a jour',
            'description'=>'subDescData',
            'logo'=>'/media/svg/icons/Costum/ImportSuccess.svg',
            'action'=>env('APP_HOTS_URL')."imports/download/".str_replace('/','_',$filename),
        ]));

    }

    public function failed(Throwable $exception){
        $this->user->notify(new DataImportsNotif([
            'title'=>"Erreur lors de la mise a jour des EPCI",
            'description'=>'subDescData',
            'logo'=>'/media/svg/icons/Costum/WarningReqeust.svg',
            'action'=>'/client/communities/communes',
        ]));
        // broadcast(new UserNotification([
        //     'async'=>true
        // ],$this->user->user_channel));
    }

}
