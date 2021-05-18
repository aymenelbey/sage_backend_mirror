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
        $syndicat=$syndicatQuery->orderBy("created_at","DESC")
        ->paginate($pageSize);
        return response([
            "ok"=>true,
            "data"=> $syndicat
        ],200);
    }
    public function show(Request $request){
        if(!empty($request['idSyndicat'])){
            $idSyndicat=$request['idSyndicat'];
            $syndicat=Syndicat::with('contacts')
            ->with('epics')
            ->with('sites')
            ->find($idSyndicat);
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
            "serin"=>["required","string"],
            "denominationLegale"=>["required","string"],
            "amobe"=>["required","exists:enemurations,id_enemuration"],
            'nature_juridique'=>["required","exists:enemurations,id_enemuration"],
            'departement_siege'=>["required","exists:enemurations,id_enemuration"],
            'competence_dechet'=>["required","exists:enemurations,id_enemuration"],
            'region_siege'=>["required","exists:enemurations,id_enemuration"],
            'adresse'=>['required']
        ],
        [
            "required"=>"the :attribute is required",
            "string"=>"the :attribute must be a string",
            "exists"=>":attribute doit être existe" 
        ]);
        $client = Collectivite::create([
            "typeCollectivite"=>"Syndicat"
        ]);
        $syndicat = Syndicat::create([
            "nomCourt"=>$request["nomCourt"],
            "denominationLegale"=>$request["denominationLegale"],
            "serin"=>$request["serin"],
            "lat"=>$request['lat'],
            'lang'=>$request['lang'],
            "adresse"=>$request["adresse"],
            "siteInternet"=>isset($request["siteInternet"])?$request["siteInternet"]:null,
            "telephoneStandard"=>isset($request["telephoneStandard"])?$request["telephoneStandard"]:null,
            "nombreHabitant"=>isset($request["nombreHabitant"])?$request["nombreHabitant"]:null,
            "logo"=>isset($request["logo"])?$request["logo"]:null,
            "GEDRapport"=>isset($request["GEDRapport"])?$request["GEDRapport"]:null,
            "amobe"=>$request["amobe"],
            "nature_juridique"=>$request["nature_juridique"],
            "departement_siege"=>$request["departement_siege"],
            "competence_dechet"=>$request["competence_dechet"],
            "region_siege"=>$request["region_siege"],
            "id_collectivite"=>$client->id_collectivite
        ]);
        if(isset($request["epics"])&&sizeof($request["epics"])>0){
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
        }
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
            "serin"=>["required","string"],
            "denominationLegale"=>["required","string"],
            "amobe"=>["required","exists:enemurations,id_enemuration"],
            'nature_juridique'=>["required","exists:enemurations,id_enemuration"],
            'departement_siege'=>["required","exists:enemurations,id_enemuration"],
            'competence_dechet'=>["required","exists:enemurations,id_enemuration"],
            'region_siege'=>["required","exists:enemurations,id_enemuration"],
            'adresse'=>['required']
        ]);
        $syndicat=Syndicat::find($request['id_syndicat']);
        $collectRequest=collect($request);
        $syndicat->update($collectRequest->only(["nomCourt","denominationLegale","serin","lat",'lang',"adresse","siteInternet","telephoneStandard","nombreHabitant","logo","GEDRapport","amobe","nature_juridique","departement_siege","competence_dechet","region_siege"])->toArray());
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
        $syndicat=Syndicat::find($request['idSyndicat']);
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