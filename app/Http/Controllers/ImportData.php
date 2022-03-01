<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Syndicat;

use App\Models\EPIC;
use App\Models\Commune;
use App\Models\Site;
use App\Models\Contact;

use Maatwebsite\Excel\Facades\Excel;

use App\Imports\CollectionsImport;
use App\Exports\CollectionsExport;


class ImportData extends Controller
{
    const JOBS_PROCESS=[
        'Syndicats'=>'\App\Jobs\ImportSyndicats',
        'Regions'=>'\App\Jobs\ImportRegions',
        'Departments'=>'\App\Jobs\ImportDepartments',
        'Epics'=>'\App\Jobs\ImportEpics',

        'Communes'=>'\App\Jobs\ImportCommunes',
        'CommunesMAJ'=>'\App\Jobs\ImportCommunesMAJ',
        
        'Companies'=>'\App\Jobs\ImportCompanies',
        'Compositions'=>'\App\Jobs\ImportCompositionSyndicat',
        'Gestionaires'=>'\App\Jobs\ImportGestionaires',

        'SitesTMB'=>'\App\Jobs\ImportSitesTMB',
        'SitesTMBMAJ'=>'\App\Jobs\ImportSitesTMB',

        'SitesISDND'=>'\App\Jobs\ImportSitesISDND',
        'SitesISDNDMAJ'=>'\App\Jobs\ImportSitesISDND',
        
        'SitesUVE'=>'\App\Jobs\ImportSitesUVE',
        'ContractUVEExploitant'=>'\App\Jobs\ImportContractUVEExploitant',
        'Contacts'=>'\App\Jobs\ImportContacts',
        'ContactsMAJ'=>'\App\Jobs\ImportContactsMAJ',
        'EpicsMAJ'=>'\App\Jobs\ImportEpicsMAJ',
    ];
    public function import(Request $request)
    {
        $this->validate($request, [
            'file'=>['required','file','mimes:xlsx'],
            'typeData'=>['required','in:Syndicats,Regions,Departments,Epics,Communes,Companies,Compositions,Gestionaires,SitesTMB,SitesISDND,SitesUVE,ContractUVEExploitant,Contacts,EpicsMAJ,CommunesMAJ,ContactsMAJ,SitesTMBMAJ,SitesISDNDMAJ']
        ]);
        $user=auth()->user();
        $path = $request->file('file')->store("imports/$request->typeData/$user->id");
        (self::JOBS_PROCESS[$request->typeData])::dispatch($path,$user);
        return response([
            'message'=>"Import Started",
            'type' => $request->typeData,
            "path" => $path,
            "job" => self::JOBS_PROCESS[$request->typeData],
            'ok' => true
        ]);
    }
    public function download_excel(Request $request)
    {
        $fileName=str_replace('_','/',$request['filename']).".xlsx";
        return response()->download(storage_path('app/'.$fileName));
    }
    public function download_update_file(Request $request){
        $type = $request->type;

        switch($type){
            case 'EpicsMAJ': {
                $epics = EPIC::get();
                $excel = [];
                
                foreach($epics as $epic){
                    array_push($excel, [
                        'Siret' => $epic['serin'],
                        'Nom court' => $epic['nom_court'],
                        'effectif' => $epic['nombreHabitant']
                    ]);
                }

                return Excel::download(new CollectionsExport($excel),"EPCI_MAJ.xlsx");
            }
            case 'SyndicatsMAJ': {
                $syndicats = Syndicat::get();
                $excel = [];
                
                foreach($syndicats as $item){
                    // $item->withEnums();
                    array_push($excel, [
                        "SIRET" => $item['serin'],
                        "Nom court" => $item['nomCourt'],
                        "Dénomination légale"=> $item['denominationLegale'],
                        "Code SINOE"=> $item['sinoe'],
                        'Nature juridique'=> $item->nature_juridique,
                        'Code du département'=> $item->departement_siege,
                        'region_siege' => $item->region_siege,
                        "Site web"=> $item['siteInternet'],
                        "Téléphone"=> $item['telephoneStandard'],
                        "Mail"=> $item['email'],
                        "Adresse"=> $item['adresse'],
                        "Libelle commune etablissement" =>$item['city'],
                        "Tranche effectifs unite legale" =>$item['nombreHabitant'],
                        "Annee effectifs unite legale" => explode('-',$item['date_enter'])[0],
                        "Code postal etablissement" => $item['postcode']
                    ]);
                }
                return $excel;
                return Excel::download(new CollectionsExport($excel),"Syndicats_MAJ.xlsx");
            }
            case 'CommunesMAJ': {
                $communes = Commune::get();
                $excel = [];
                
                foreach($communes as $commune){
                    array_push($excel, [
                        'Siren' => $commune['serin'],
                        'Nom commune' => $commune['nomCommune'],
                        'Effectif' => $commune['nombreHabitant']
                    ]);
                }

                return Excel::download(new CollectionsExport($excel),"COMMUNE_MAJ.xlsx");
            }
            case 'SitesTMBMAJ': {
                $sites = Site::with('dataTech.dataTech')->where('categorieSite', 'TMB')->limit(10)->get();
                $excel = [];

                foreach($sites as $site){
                    if(!$site->dataTech || !$site->dataTech->dataTech){
                        continue;
                    }
                    $site->dataTech->dataTech->withEnums();
                    $data = $site->dataTech->dataTech;

                    array_push($excel, [
                        'SINOE' => $site->sinoe,
                        'Mode de gestion' => $site->modeGestion,
                        'Type dinstallations' => $data->typeInstallation,
                        'Technologie' => isset($data->technologie[0]) ? $data->technologie[0]['value_enum'] : null,
                        'Tonnage annuel' => $data->tonnageAnnuel,
                        'Capacité nominale' => $data->capaciteNominal,
                        'Types de déchets acceptés' => isset($data->typeDechetAccepter[0]) ? $data->typeDechetAccepter[0]['value_enum'] : null,
                        'Autres activités sur site' => isset($data->autreActivite[0]) ? $data->autreActivite[0]['value_enum'] : null,
                        'Quantité de refus t' => $data->quantiteRefus,
                        'CSR produit t exutoire' => $data->CSRProduit,
                        'Envoi pour préparation CSR t' => $data->envoiPreparation,
                        'Valorisation énergétique' => isset($data->valorisationEnergitique[0]) ? $data->valorisationEnergitique[0]['value_enum'] : null,
                    ]);
                }

                return Excel::download(new CollectionsExport($excel),"SitesTMB_MAJ.xlsx");
            }
            case 'SitesISDNDMAJ': {
                $sites = Site::with('dataTech.dataTech')->where('categorieSite', 'ISDND')->limit(10)->get();
                $excel = [];
                
                foreach($sites as $site){
                    $site->dataTech->dataTech->withEnums();
                    $data = $site->dataTech->dataTech;

                    array_push($excel, [
                        'SINOE' => $site->sinoe,
                        'Capacité nominale' => $data->capaciteNominale,
                        'Capacité restante' => $data->capaciteRestante,
                        'Capacité réglementaire' => $data->capaciteReglementaire,
                        'Projet dextension' => $data->projetExtension == 1 ? 'oui' : 'non',
                        'Date dextension' => $data->dateExtension,
                        'Date douverture' => $data->dateOuverture,
                        'Date de fermeture' => $data->dateFermeture,
                        'Date de fermeture prévisionnelle' => $data->dateFermeturePrev,

                    ]);

                }
                
                return Excel::download(new CollectionsExport($excel),"SitesISDND_MAJ.xlsx");
            }
            case 'ContactsMAJ': {
                $contacts = Contact::with('persons_moral')->get();
                $output = [];

                foreach($contacts as $contact){
                    $result = [
                        'ID contact' => $contact->id_contact,
                        'civilite' => $contact->genre,
                        'prenom'  => $contact->prenom,
                        'nom'  => $contact->nom,
                        'telephone'  => $contact->telephone,
                        'mobile'  => $contact->mobile,
                        'email'  => $contact->email,
                        'adresse'  => $contact->address,
                        'status'  => $contact->status == '1' ? 'actif' : 'inactif',
                        'informations'  => $contact->informations,
                        'communes' => [],
                        'epcis' => [],
                        'syndicats' => [],
                        'supprimer' => 'non'
                    ];
                    
                    foreach($contact->persons_moral as $person_moral){
                        switch($person_moral->typePersonMoral){
                            case 'Commune':
                                if(isset($person_moral->fonction_person[0])){
                                    $result['communes'][] = $person_moral->person->insee.':'.$person_moral->fonction_person[0]->getFunctionStringAttribute();
                                }
                                break;
                            case 'Epic': 
                                if(isset($person_moral->fonction_person[0])){
                                    $result['epcis'][] = $person_moral->person->serin.':'.$person_moral->fonction_person[0]->getFunctionStringAttribute();
                                }
                                break;
                            case 'Syndicat';
                                if(isset($person_moral->fonction_person[0])){
                                    $result['syndicats'][] = $person_moral->person->serin.':'.$person_moral->fonction_person[0]->getFunctionStringAttribute();
                                }
                                break;
                            default:
                                break;
                        }
                    }
                    
                    $result['epcis'] = implode(',', $result['epcis']);
                    $result['communes'] = implode(',', $result['communes']);
                    $result['syndicats'] = implode(',', $result['syndicats']);

                    $output[] = $result;
                }

                return Excel::download(new CollectionsExport($output),"CONTACTS_MAJ.xlsx");
            }
            default:
                return 'No Type was selected';
        }
    }
    
}