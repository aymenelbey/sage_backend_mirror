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
use App\Models\Gestionnaire;
use App\Models\User;
use App\Events\UserNotification;
use App\Notifications\DataImportsNotif;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Throwable;
use Excel;

class ImportGestionaires implements ShouldQueue
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
        $socites=[
            'SAGE ENGINEERING'=>'Sage_engineering',
            'SAGE INDUSTRY'=>'Sage_industry',
            'SAGE EXPERT'=>'Sage_expert'
        ];
        foreach($dataImport as $item){
            $email_exist = Gestionnaire::where('email',$item['email'])->exists();
            if(!$email_exist 
                    && isset($item['nom']) && !empty($item['nom']) && isset($item['prenom']) && !empty($item['prenom']) 
                    && in_array($item['civilite'],['MME','MR']) 
                    && in_array($item['societe'],['SAGE ENGINEERING','SAGE INDUSTRY','SAGE EXPERT'])){
                $username=User::getUsername($item['nom'],$item['prenom']);
                $password=Str::random(10);
                $user =  User::create([
                    "username"=>$username,
                    "typeuser"=>"Gestionnaire",
                    "password"=>Hash::make($password),
                    "init_password"=>$password
                ]);
                Gestionnaire::create([
                    "status"=>true,
                    "genre"=>$item["civilite"],
                    "nom"=>$item["nom"],
                    "prenom"=>$item["prenom"],
                    "mobile"=>$item["mobile"],
                    "email"=>$item["email"],
                    "societe"=>$socites[$item["societe"]],
                    'id_user'=>$user->id,
                    "id_admin"=>1
                ]);
            }else{
                $item['Problem trouvé'] = '';
                if($email_exist){
                    $item['Problem trouvé'] .= 'Email existante, ';
                }
                if(!( isset($item['nom']) && !empty($item['nom']) && isset($item['prenom']) && !empty($item['prenom']) )){
                    $item['Problem trouvé'] .= 'Nom ou prénom invalid, ';
                }
                if(!(in_array($item['societe'],['SAGE ENGINEERING','SAGE INDUSTRY','SAGE EXPERT']))){
                    $item['Problem trouvé'] .= 'Societe invalid, ';
                }
                if(!in_array($item['civilite'],['MME','MR'])){
                    $item['Problem trouvé'] .= 'Civilité invalide';
                }
                $ignoredData []=$item;
            }
        }
        $filename="exports/Gestionaires/".md5("gestionaires_exports".time());
        $fileResult=Excel::store(new CollectionsExport($ignoredData), $filename.".xlsx");
        $this->user->notify(new DataImportsNotif([
            'title'=>'La list des Gestionaires importé avec succès',
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
            'title'=>"Erreur lors de l'importation des gestionaires",
            'description'=>'subDescData',
            'logo'=>'/media/svg/icons/Costum/WarningReqeust.svg',
            'action'=>'/manager',
        ]));
        // broadcast(new UserNotification([
        //     'async'=>true
        // ],$this->user->user_channel));
    }
}