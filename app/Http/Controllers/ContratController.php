<?php

namespace App\Http\Controllers;

use App\Models\Contrat;
use App\Models\SocieteExploitant;
use App\Models\CommunHasContrat;
use App\Models\Enemuration;
use Illuminate\Http\Request;
use Validator;
use App\Jobs\Export\ExportContrats;
use App\Exports\ArrayExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Helpers\ExportHelper;

class ContratController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        $query = Contrat::query();
        $pageSize=$request->get('pageSize')?$request->get('pageSize'):10;
        $query = $query->with(['contractant', 'site', 'communes']);
        $contra = $query->orderBy("created_at","ASC")->paginate($pageSize)->toArray();
        foreach($contra['data'] as &$contract){
            if(isset($contract['contractant']) && !empty($contract['contractant']) && isset($contract['contractant']['groupe'])){
                    $contract['contractant']['groupe'] = SocieteExploitant::getGroupeStatic($contract['contractant']['groupe']); 
                    // $contract['contractant']['groupe'] = Enemuration::whereIn('id_enemuration', is_array($contract['contractant']['groupe']) ? $contract['contractant']['groupe'] : [$contract['contractant']['groupe']])->get();
            }else{
                $contract['contractant']['groupe'] = [];
            }
        }
        return response([
            "ok"=>"server",
            "data"=>$contra
        ],200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request){
        $this->validate($request,[
            "dateDebut"=>[],
            "dateFin"=>[],
            "communes"=>['required',"array"],
            "site"=>["exists:sites,id_site"],
            "contractant"=>["exists:societe_exploitants,id_societe_exploitant"]
        ],
        [
            "communes.required" => "Le champ Acteur public est obligatoire."
        ]);
        $contrat =  Contrat::create([
            "dateDebut"=>$request["dateDebut"],
            "dateFin"=>$request["dateFin"],
            "autreActivite"=>$request["autreActivite"],
            "id_site"=>$request["site"],
            "contractant"=>$request["contractant"]
        ]);
        foreach($request['communes'] as $commune){
            $comHas =  CommunHasContrat::create([
                "id_contrat"=>$contrat->id_contrat,
                "typePersonMoral"=>$commune['type'],
                "idPersonMoral"=>$commune['id_person']
            ]);
        }
        return response([
            "ok"=>true,
            "data"=>$contrat
        ],200);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Contrat  $contrat
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $this->validate($request,[
            "id_contrat"=>["required","exists:contrats"],
            "dateDebut"=>[],
            "dateFin"=>[],
            "communes"=>['required',"array"],
            "site"=>["exists:sites,id_site"],
            "contractant"=>["exists:societe_exploitants,id_societe_exploitant"]
        ],
        [
            "communes.required" => "Le champ Acteur public est obligatoire."
        ]);
        $contrat = Contrat::find($request["id_contrat"]);
        $contrat->update([
            "dateDebut"=>$request["dateDebut"],
            "dateFin"=>$request["dateFin"],
            "autreActivite"=>$request["autreActivite"],
            "id_site"=>$request["site"],
            "contractant"=>$request["contractant"]
        ]);
        $ignorekey=[];
        $listCommune=array_column($request["communes"],'id_person');
        $prevCommunes=CommunHasContrat::where('id_contrat',$request['id_contrat'])->get();
        foreach($prevCommunes as $comune){
            $keySearch=array_search($comune->id_person,$listCommune);
            if($keySearch>-1 && $listCommune['type']==$comune->typePersonMoral){
                $ignorekey[]=$keySearch;
            }else{
                $comune->delete();
            }
        } 
        foreach($request["communes"] as $key=>$commune){
            if(!in_array($key,$ignorekey)){
                $comnCont = CommunHasContrat::create([
                    "id_contrat"=>$contrat->id_contrat,
                    "typePersonMoral"=>$commune['type'],
                    "idPersonMoral"=>$commune['id_person']
                ]);
            }
        }
        return response([
            "ok"=>true,
            "data"=> $contrat,
            "test"=>$ignorekey
        ],200);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Contrat  $contrat
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $idcontract=$request['idContract'];
        $contract=Contrat::with('site', 'updated_by')
        ->with('contractant')
        ->with('communes')
        ->find($idcontract)->toArray();

        if(isset($contract['contractant']) && isset($contract['contractant']['groupe'])){
            $contract['contractant']['groupe'] = SocieteExploitant::getGroupeStatic($contract['contractant']['groupe']); 
        }
    

        return response([
            'ok'=>true,
            'data'=>$contract
        ],200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        $idcontract=$request['idContract'];
        $contract = Contrat::with("contractant")->with("site")->with("communes")->find($idcontract)->toArray();
        
        if(isset($contract['contractant']) && isset($contract['contractant']['groupe'])){
            $contract['contractant']['groupe'] = SocieteExploitant::getGroupeStatic($contract['contractant']['groupe']); 
        }
    
        return response([
            'ok'=>true,
            'data'=>$contract
        ],200);
    }

    

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Contrat  $contrat
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $rules = [
            "contracts"=>["required","array"]
        ];
        $message = [];
        $validator = Validator::make($request->all(),$rules,$message);
        if($validator->fails()){
            return response([
                "ok"=>"server",
                "errors"=>$validator->errors()
            ],400);
        }
        $contracts=$request['contracts'];
        $deletedLis=[];
        $notDeletedLis = [];
        foreach($contracts as $contract_id){
            try{
                $contract = Contrat::find($contract_id);
                if($contract){
                    $canDelete = $contract->canDelete();
                    if($canDelete['can']){
                        Contrat::destroy($contract_id);
                        $deletedLis[] = $contract_id;
                    }else{
                        $notDeletedLis[$contract_id] = $canDelete['errors'];
                    }
                }
            }catch(\Exception $e){
                $notDeletedLis[$epic] = ['db.destroy-error'];
            }

            if(sizeof($request['contracts']) == 1 && sizeof($notDeletedLis) == 1){
                return response([
                    "errors" => true,
                    "message" => "item already in use",
                    "reasons" => $notDeletedLis
                ], 402);
            }

        }
        return response([
            'ok'=>true,
            'data'=>"async",
            'contracts'=>$deletedLis,
            'not_deleted' => $notDeletedLis
        ]);
    }

    public function export(Request $request) {
        ExportContrats::dispatch($request->user(), "contrats", "/contrat");

        return response([
            "ok" => true,
            "data" => "no action",
        ], 200);
    }

    public function export_model(Request $request) {
        $count_acteurs = 1;
        $structure = [
            "site" => [
                "type" => "child",
                "structure" => [
                    "sinoe" => "value",
                    "denomination" => "value",
                    "categorieSite" => "value",
                    "modeGestion" => "value",
                    "city" => "value"
                ],
                "prefix" => "Site - "
            ],
            "communes" => [
                "type" => "list",
                "structure" => [
                    "typePersonMoral" => "value",
                    "siren" => "value",
                    "dataIndex" => "ref",
                    "city" => "value"
                ],
                "prefix" => "acteurs",
                "mapping" => [
                    "typePersonMoral" => "type",
                    "nomEpic" => "nom",
                    "nomCourt" => "nom",
                    "nomCommune" => "nom",
                    "dataIndex" => "nom"
                ],
                "prefix" => "acteur",
                "count" => $count_acteurs
            ],
            "contractant" => [
                "type" => "child",
                "structure" => [
                    "sinoe" => "value",
                    "groupe" => "enum_array",
                    "denomination" => "value"
                ],
                "prefix" => "Contractant - "
            ],
            "dateDebut" => "value",
            "dateFin" => "value"
        ];
        $mapping = [
            "sinoe" => "Sinoe",
            "denomination" => "Dénomination",
            "categorieSite" => "Catégorie",
            "modeGestion" => "Mode de gestion",
            "city" => "Ville",
            "serin" => "Siren",
            "groupe" => "Groupe",
            "dateDebut" => "Début du Contrat",
            "dateFin" => "Fin du Contrat"
        ];
        return Excel::download(new ArrayExport(ExportHelper::get_headings($structure, null, $mapping)), 'contrats_export_model.xlsx');
    }
}