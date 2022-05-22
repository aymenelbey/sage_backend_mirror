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
use App\Models\Departement;
use App\Events\UserNotification;
use App\Notifications\DataImportsNotif;
use Throwable;
use Excel;

class ImportDepartments implements ShouldQueue
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
            if(isset($item['code_dep']) && isset($item['region_code']) && isset($item['lib_dep']) && !empty($item['code_dep']) && !empty($item['region_code']) && !empty($item['lib_dep'])){
                $code = strlen($item['code_dep']) == 1 ? '0'.$item['code_dep'] : $item['code_dep'];
                $region_code = strlen($item['region_code']) == 1 ? '0'.$item['region_code'] : $item['region_code'];
                Departement::updateOrCreate([
                    "departement_code" => $code,
                ], [
                    "region_code" => $region_code,
                    "name_departement" => $item['lib_dep']
                ]);
            }else{
                $item['Problem trouvé'] = '';
                
                if(empty($item['code_dep'])){
                    $item['Problem trouvé'] .= 'Code departement requis';
                }

                if(empty($item['lib_dep'])){
                    $item['Problem trouvé'] .= ', Label departement requis';
                }

                if(empty($item['region_code'])){
                    $item['Problem trouvé'] .= ', Code region requis';
                }

                $ignoredData []=$item;
            }
        }
        $filename="exports/Departments/".md5("departments_exports".time());
        $fileResult=Excel::store(new CollectionsExport($ignoredData), $filename.".xlsx");
        $this->user->notify(new DataImportsNotif([
            'title'=>'La list des departements importé avec succès',
            'description'=>'subDescData',
            'logo'=>'/media/svg/icons/Costum/ImportSuccess.svg',
            'action'=>env('APP_HOTS_URL')."imports/download/".str_replace('/','_',$filename),
        ]));
        // broadcast(new UserNotification([
        //     'async'=>true
        // ],$this->user->user_channel));
    }
    public function failed(Throwable $exception)
    {
        $this->user->notify(new DataImportsNotif([
            'title'=>"Erreur lors de l'importation des departements",
            'description'=>'subDescData',
            'logo'=>'/media/svg/icons/Costum/WarningReqeust.svg',
            'action'=>'/client/communities/communes',
        ]));
        // broadcast(new UserNotification([
        //     'async'=>true
        // ],$this->user->user_channel));
    }
}