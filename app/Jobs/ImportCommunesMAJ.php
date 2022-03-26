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
use App\Models\Commune;
use Carbon\Carbon;

use App\Models\InfoClientHistory;

use App\Events\UserNotification;
use App\Notifications\DataImportsNotif;
use Throwable;
use Excel;


class ImportCommunesMAJ implements ShouldQueue
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

        foreach($dataImport as $commune){

            $dbCommune = Commune::where('serin', $commune['siren'])->first();


            if(!$dbCommune || $dbCommune->nombreHabitant == $commune['effectif']){
                // append to file
                $ignoredData[] = $commune;
                continue;
            }
            
            InfoClientHistory::customCreate([
                'id_reference' => $dbCommune->id_commune,
                'referenced_table' => "Commune",
                'referenced_column' => 'nombreHabitant',
                'date_reference' => $dbCommune->date_enter,
                'prev_value' => $dbCommune->nombreHabitant
            ]);
            
            if(!$dbCommune->update(['nombreHabitant' => $commune['effectif'], 'date_enter'=> Carbon::now()])){
                $ignoredData[] = $commune;
            }
        }


        $filename="exports/Communes/".md5("communes_exports_maj".time());
        $fileResult=Excel::store(new CollectionsExport($ignoredData), $filename.".xlsx");

        $this->user->notify(new DataImportsNotif([
            'title'=>'La list des Communes non mise a jour',
            'description'=>'subDescData',
            'logo'=>'/media/svg/icons/Costum/ImportSuccess.svg',
            'action'=>env('APP_HOTS_URL')."imports/download/".str_replace('/','_',$filename),
        ]));

    }

    public function failed(Throwable $exception){
        $this->user->notify(new DataImportsNotif([
            'title'=>"Erreur lors de la mise a jour des Communes",
            'description'=>'subDescData',
            'logo'=>'/media/svg/icons/Costum/WarningReqeust.svg',
            'action'=>'/client/communities/communes',
        ]));
        // broadcast(new UserNotification([
        //     'async'=>true
        // ],$this->user->user_channel));
    }

}
