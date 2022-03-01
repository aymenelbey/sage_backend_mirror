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
use App\Events\UserNotification;
use App\Notifications\DataImportsNotif;


use App\Models\Commune;
use App\Models\Contact;
use App\Models\ContactHasPersonMoral;
use App\Models\PersonFunction;
use App\Models\EPIC;
use App\Models\DataTechnTMB;
use App\Models\User;
use App\Models\GestionnaireHasSite;
use App\Models\SocieteExpSite;
use App\Models\ClientHasSite;
use App\Models\SocieteExploitant;
use App\Models\Syndicat;
use App\Models\DataTechn;
use App\Models\Site;
use App\Models\Region;
use App\Models\Departement;

use App\Http\Helpers\ToolHelper;
use App\Constants\Constants;
use Throwable;
use Excel;
use Illuminate\Support\Facades\Log;

class ImportContactsMAJ implements ShouldQueue
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
        // $dataImport = array_slice($dataImport, 0, 1); // used for testing
        $delete = [];

        $fonctions = array_merge([], array_filter(array_column($dataImport, 'communes')));
        $fonctions = array_merge($fonctions, array_filter(array_column($dataImport, 'epcis')));
        $fonctions = array_merge($fonctions, array_filter(array_column($dataImport, 'syndicats')));
        
        // print_r($fonctions);die();
        $fonctions = array_map(function($cell){
            if(! $cell || empty($cell)) return;
    
            $positions = array_map(function($position){
                if(empty($position)) return;
    
                return explode(':', $position)[1];
            }, explode(',', $cell));
            return $positions;
        }, $fonctions);
        
        $fonctions = array_reduce($fonctions, function($acc, $positions){
            return array_merge($acc, $positions);
        }, []);
        
        $fonctions = array_unique($fonctions); // list of all fonction
        
        $new_fonctions = [];
        foreach($fonctions as $fonction){
            $db_function = Enemuration::where('key_enum','function_person')->where('value_enum', $fonction)->first();
            if($db_function){
                $new_fonctions[$fonction] = $db_function; 
            }else{
                $new_fonctions[$fonction] = Enemuration::create(['key_enum' => 'function_person', 'value_enum' => $fonction]); 
            }
        }


        $ignoredData=[];

        foreach($dataImport as $contact){
            
            $contact['problème trouvé'] = '';

            if(!$contact['id_contact']){
                $contact['problème trouvé'] = 'ID non trouvé';
                $ignoredData[] = $contact; 
                continue;
            }

            if($contact['supprimer'] == 'oui'){
                $delete[] = $contact['id_contact'];
                continue;
            }

            $DBcontact = Contact::with('persons_moral')->find($contact['id_contact']);
            
            if(!$DBcontact){
                $contact['problème trouvé'] = 'ID non trouvé';
                $ignoredData[] = $contact; 
                continue;
            }

            $DBcontact->update([
                "status" => $contact['status'] == 'actif',
                "genre" => $contact['civilite'],
                "nom" => $contact['nom'],
                "prenom" => $contact['prenom'],
                "telephone" => $contact['telephone'],
                "mobile" => $contact['mobile'],
                "email" => $contact['email'],
                "informations" => $contact['informations'],
                'address' => $contact['adresse']
            ]);
            
            
            foreach($DBcontact->persons_moral as $ContactHasPersonMoral){
                // foreach($ContactHasPersonMoral->fonction_person as $fonction_person) $fonction_person->forceDelete();
                $ContactHasPersonMoral->delete();
            }

            $communes = array_map(function($fon){
                return explode(':', $fon);
            }, explode(',', $contact['communes']));
            
            $epcis = array_map(function($fon){
                return explode(':', $fon);
            }, explode(',', $contact['epcis']));

            $syndicats = array_map(function($fon){
                return explode(':', $fon);
            }, explode(',', $contact['syndicats']));

            if(sizeof($communes) > 0){
                foreach($communes as $commune){
                    $commune_id = Commune::where('insee', $commune[0])->first();

                    if(!$commune_id){
                        // echo 'Commune not found insee '.$commune[0];
                        $contact['problème trouvé'] .= 'Commune non trouvée'.$commune[0].', ';
                        continue;
                    }
                    
                    $commune_id = $commune_id->id_commune;

                    $fonction = $commune[1];

                    $contactCollect = ContactHasPersonMoral::create([
                        "idPersonMoral" => $commune_id,
                        "typePersonMoral" => 'Commune',
                        "id_contact" => $DBcontact->id_contact
                    ]);
                    PersonFunction::create([
                        "functionPerson"=> $new_fonctions[$fonction]->id_enemuration,
                        "id_person"=> $contactCollect->id_contact_has_person_morals
                    ]);
                }
            }

            if(sizeof($epcis) > 0){

                foreach($epcis as $epci){
                    $epci_id = EPIC::where('serin', $epci[0])->first();

                    if(!$epci_id){
                        $contact['problème trouvé'] .= 'EPCI non trouvée'.$epci[0].', ';
                        continue;
                    }
                    
                    $epci_id = $epci_id->id_epic;

                    $fonction = $epci[1];

                    $contactCollect = ContactHasPersonMoral::create([
                        "idPersonMoral" => $epci_id,
                        "typePersonMoral" => 'Epic',
                        "id_contact" => $DBcontact->id_contact
                    ]);

                    PersonFunction::create([
                        "functionPerson"=> $new_fonctions[$fonction]->id_enemuration,
                        "id_person"=> $contactCollect->id_contact_has_person_morals
                    ]);
                }
            }

            if(sizeof($syndicats) > 0){
                foreach($syndicats as $syndicat){
                    $syndicat_id = Syndicat::where('serin', $syndicat[0])->first();

                    if(!$syndicat_id){
                        $contact['problème trouvé'] .= 'Syndicat non trouvée'.$syndicat[0].', ';
                        continue;
                    }
                    
                    $syndicat_id = $syndicat_id->id_syndicat;

                    $fonction = $syndicat[1];

                    $contactCollect = ContactHasPersonMoral::create([
                        "idPersonMoral" => $syndicat_id,
                        "typePersonMoral" => 'Syndicat',
                        "id_contact" => $DBcontact->id_contact
                    ]);
                    PersonFunction::create([
                        "functionPerson"=> $new_fonctions[$fonction]->id_enemuration,
                        "id_person"=> $contactCollect->id_contact_has_person_morals
                    ]);
                }
            }

        }
        
        Contact::whereIn('id_contact', $delete)->delete();

        $filename= "exports/Contacts/".md5("contacts_maj".time());
        $fileResult= Excel::store(new CollectionsExport($ignoredData), $filename.".xlsx");
        $this->user->notify(new DataImportsNotif([
            'title'=>'La list des contacts non importé',
            'description'=>'subDescData',
            'logo'=>'/media/svg/icons/Costum/ImportSuccess.svg',
            'action'=>env('APP_HOTS_URL')."imports/download/".str_replace('/','_',$filename),
        ]));

    }

    public function failed(Throwable $exception){
        $this->user->notify(new DataImportsNotif([
            'title'=>"Erreur lors de mise a jour des contacts",
            'description'=>'subDescData',
            'logo'=>'/media/svg/icons/Costum/WarningReqeust.svg',
            'action'=>'/sites',
        ]));
        // broadcast(new UserNotification([
        //     'async'=>true
        // ],$this->user->user_channel));
    }
}
