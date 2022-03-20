<?php

namespace App\Http\Controllers;

use App\Models\Contrat;
use App\Models\CommunHasContrat;
use App\Models\Enemuration;
use Illuminate\Http\Request;
use Validator;

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
        $query = $query->with(['contractant', 'site']);
        $contra = $query->orderBy("created_at","DESC")->paginate($pageSize)->toArray();
        foreach($contra['data'] as &$contract){
            if(isset($contract['contractant'])){
                $contract['contractant']['groupe'] = Enemuration::whereIn('id_enemuration', is_array($contract['contractant']['groupe']) ? $contract['contractant']['groupe'] : [$contract['contractant']['groupe']])->get();
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
        $contract=Contrat::with('site')
        ->with('contractant')
        ->with('communes')
        ->find($idcontract);
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
        $contract=Contrat::with("contractant.groupe")
        ->with("site")
        ->with("communes")
        ->find($idcontract)
        ->toArray();
        $contract['contractant']['groupe']=$contract['contractant']['groupe']['value_enum'];
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
        foreach($contracts as $contract){
            $contrObj=Contrat::find($contract);
            if($contrObj){
                $deletedLis [] = $contract;
                $contrObj->delete();
            }
        }
        return response([
            'ok'=>true,
            'data'=>"async",
            'contracts'=>$deletedLis
        ]);
    }
}