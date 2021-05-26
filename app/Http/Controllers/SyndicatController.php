<?php

namespace App\Http\Controllers;

use App\Models\Syndicat;
use App\Models\Collectivite;
use App\Models\SyndicatHasEpic;
use Illuminate\Http\Request;
use Validator;
use Carbon\Carbon;

class SyndicatController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request){
        $nomCourt=$request->get('nomCourt');
        $address=$request->get('address');
        $function='where';
        $pageSize=$request->get('pageSize')?$request->get('pageSize'):10;
        $syndicatQuery = Syndicat::query();
        if($nomCourt){
            $syndicatQuery=$syndicatQuery->{$function}("nomCourt","ILIKE","%{$nomCourt}%");
            $function='orWhere';
        }
        if($address){
            $syndicatQuery=$syndicatQuery->{$function}("adresse","ILIKE","%{$address}%");
            $function='orWhere';
        }
        $syndicat=$syndicatQuery->orderBy("id_syndicat","ASC")
        ->paginate($pageSize);
        return response([
            "ok"=>true,
            "data"=> $syndicat
        ],200);
    }
    public function show(Request $request){
        if(!empty($request['idSyndicat'])){
            $idSyndicat=$request['idSyndicat'];
            $syndicat=Syndicat::with(['contacts','epics','sites','logo','ged_rapport'])
            ->find($idSyndicat);
            $syndicat->withEnums();
            $syndicat=$syndicat->toArray();
            if(!empty($syndicat["logo"][0])){
                $syndicat["logo"]=$syndicat["logo"][0]["url"];
            }
            if(!empty($syndicat["ged_rapport"][0])){
                $syndicat["ged_rapport"]=$syndicat["ged_rapport"][0];
            }
            return response([
                'ok'=>true,
                'data'=>$syndicat
            ],200);
        }
        return response([
            'ok'=>'server',
            'errors'=>'Aucune syndicat disponible'
        ],400);
    }
    public function updateEpic(Request $request){
        $validator = Validator::make($request->all(),[
            "id_syndicat"=>"exists:syndicats",
            "epics"=>"required"
        ],[
            "required"=>":attribute est obligatoire",
            "exists"=>":attribute n'existe pas dans la list des syndicats"
        ]);
        if($validator->fails()){
            return response([
                "ok"=>false,
                "message"=>$validator->errors()
            ],400);
        }
        if(sizeof($request["epics"])>0){
            foreach($request["epics"] as $id){
                $validator = Validator::make(["id_epic"=>$id],["id_epic"=>"exists:epics"],["exists"=>"verifier que les epics existes"]);
                if($validator->fails()){
                    return response([
                        "ok"=>false,
                        "message"=> $validator->errors()
                    ],400);
                } 
            } 
            $array_syndica = [];
            foreach($request["epics"] as $id){
                $temp = [
                    "id_epic"=>$id,
                    "id_syndicat"=>$request["id_syndicat"],
                    "created_at"=>Carbon::now(),
                    "updated_at"=>Carbon::now()
                ];
                array_push($array_syndica,$temp);
            }
            $syndic = SyndicatHasEpic::insert($array_syndica);
        }
        return response([
            "ok"=>true,
            "message"=>$syndic
        ]);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request){
        $this->validate($request,[
            "nomCourt"=>["required","string"],
            "serin"=>["required","numeric","digits_between:1,14"],
            "sinoe"=>["required"],
            "email"=>["nullable","email"],
            "logo"=>["nullable","uuid","exists:image_sages,uid"],
            "ged_rapport"=>["nullable","uuid","exists:image_sages,uid"],
            "denominationLegale"=>["required","string"],
            'nature_juridique'=>["required","exists:enemurations,id_enemuration"],
            'departement_siege'=>["required","exists:enemurations,id_enemuration"],
            'competence_dechet'=>["required","exists:enemurations,id_enemuration"],
            'region_siege'=>["required","exists:enemurations,id_enemuration"],
            'adresse'=>['required']
        ]);
        $client = Collectivite::create([
            "typeCollectivite"=>"Syndicat"
        ]);
        $syndicat = Syndicat::create($request->only(["nomCourt","denominationLegale","serin","adresse",'lat','lang',"siteInternet","telephoneStandard","nombreHabitant","logo","ged_rapport",'amobe','nature_juridique','departement_siege','competence_dechet','region_siege',"email","sinoe"])+['id_collectivite'=>$client->id_collectivite]);
        /*if(isset($request["epics"])&&sizeof($request["epics"])>0){
            $epics_array = [];
            foreach($request["epics"] as $id){
                $temp = [
                    "id_epic"=>$id,
                    "id_syndicat"=>$syndicat->id_syndicat,
                    'created_at'=>Carbon::now(),
                    "updated_at"=>Carbon::now()
                ];
                array_push($epics_array,$temp);
            }
            $syndhas = SyndicatHasEpic::insert($epics_array);
        }*/
        return response([
            "ok"=>true,
            "data"=>$syndicat
        ],200);
       
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Syndicat  $syndicat
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request){
        $this->validate($request,[
            "id_syndicat"=>["required","exists:syndicats"],
            "nomCourt"=>["required","string"],
            "serin"=>["required","numeric","digits_between:1,14"],
            "sinoe"=>["required"],
            "email"=>["nullable","email"],
            "logo"=>["nullable","uuid","exists:image_sages,uid"],
            "ged_rapport"=>["nullable","uuid","exists:image_sages,uid"],
            "denominationLegale"=>["required","string"],
            'nature_juridique'=>["required","exists:enemurations,id_enemuration"],
            'departement_siege'=>["required","exists:enemurations,id_enemuration"],
            'competence_dechet'=>["required","exists:enemurations,id_enemuration"],
            'region_siege'=>["required","exists:enemurations,id_enemuration"],
            'adresse'=>['required']
        ]);
        $syndicat=Syndicat::find($request['id_syndicat']);
        $syndicat->update($request->only(["nomCourt","denominationLegale","serin","adresse",'lat','lang',"siteInternet","telephoneStandard","nombreHabitant","logo","ged_rapport",'amobe','nature_juridique','departement_siege','competence_dechet','region_siege',"email","sinoe"]));
        return response([
            "ok"=>true,
            "data"=>$syndicat
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
     * @param  \App\Models\Syndicat  $syndicat
     * @return \Illuminate\Http\Response
     */
    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        $syndicat=Syndicat::with(['logo','ged_rapport'])->find($request['idSyndicat']);
        if($syndicat){
            return response([
                'ok'=>true,
                "data"=>$syndicat
            ]);
        }
        return response([
            'ok'=>false,
            "data"=>"Syndicat not found"
        ]);
    }

    

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Syndicat  $syndicat
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        if(isset($request['syndicats']) && is_array($request['syndicats'])){
            $deletedLis=[];
            foreach($request['syndicats'] as $syndicat){
                $syndica=Syndicat::find($syndicat);
                if($syndica){
                    $deletedLis [] = $syndicat;
                    $syndica->delete();
                }
            }
            return response([
                'ok'=>true,
                'data'=>"async",
                'syndicats'=>$deletedLis
            ]);
        }
        return response([
            'ok'=>true,
            'data'=>"no action"
        ]);
    }
}