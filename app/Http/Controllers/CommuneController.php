<?php

namespace App\Http\Controllers;

use App\Models\Commune;
use App\Models\EPIC;
use App\Models\Collectivite;
use Illuminate\Http\Request;
use Validator;

class CommuneController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request){
        $nomCommune=$request->get('nomCommune');
        $address=$request->get('address');
        $requireList=$request->get('list');
        $sort=$request->get('sort');
        $sorter=$request->get('sorter');
        $function='where';
        $pageSize=$request->get('pageSize')?$request->get('pageSize'):20;
        $communeQuery = Commune::query();
        if($nomCommune){
            $communeQuery=$communeQuery->{$function}("nomCommune","ILIKE","%{$nomCommune}%");
            $function='orWhere';
        }
        if($address){
            $communeQuery=$communeQuery->{$function}("adresse","ILIKE","%{$address}%");
            $function='orWhere';
        }
        if($requireList && ($nomCommune || $address)){
            $arrayData=explode(".",$requireList);
            $communeQuery=$communeQuery->{$function."In"}("id_commune",$arrayData);
            $function='orWhere';
        }
        if(in_array($sort,['ASC','DESC']) && in_array($sorter,["nomCommune","adresse","insee","serin","departement_siege","region_siege","nombreHabitant","id_commune"])){
            $communeQuery=$communeQuery->orderBy($sorter,$sort);
        }else{
           $communeQuery=$communeQuery->orderBy("id_commune","DESC");
        }
        $commune=$communeQuery->paginate($pageSize);
        return response([
            "ok"=>true,
            "data"=>$commune
        ],200);
    }
    public function show(Request $request){
        if(!empty($request['idcommune'])){
            $commune=Commune::with(['epic','contacts','logo'])
            ->find($request['idcommune']);
            $commune->withEnums();
            $commune=$commune->toArray();
            if(!empty($commune["logo"][0])){
                $commune["logo"]=$commune["logo"][0]["url"];
            }
            return response([
                'ok'=>true,
                'data'=>$commune
            ],200);
        }
        return response([
            'ok'=>'server',
            'errors'=>'Aucune epic disponible'
        ],400);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateEpic(Request $request){
        $this->validate($request,[
            "id_epic"=>["required","exists:e_p_i_c_s"],
            "id_commune"=>["required","exists:communes"]
        ]);
        $commune = Commune::where("id_commune","=",$request["id_commune"])->update(["id_epic"=>$request["id_epic"]]);
        if($commune){
            return response([
                "ok"=>true,
                "message"=>$commune
            ],200);
        }
        return response([
            "ok"=>false,
            "message"=>"Les modification ont échoé"
        ],400);
    }
    public function create(Request $request){
        $this->validate($request,[
            "nomCommune"=>["required"],
            "adresse"=>["required"],
            "serin"=>["required","numeric","digits:9"],
            "insee"=>["required","numeric","digits:5"],
            "nombreHabitant"=>["required","numeric"],
            'departement_siege'=>["required","exists:enemurations,id_enemuration"],
            'region_siege'=>["required","exists:enemurations,id_enemuration"],
            "epic"=>["required","exists:epics,id_epic"]
        ]);
        $client = Collectivite::create([
            "typeCollectivite"=>"Commune"
        ]);
        $commune = Commune::create($request->only(["nomCommune","adresse","logo","serin","insee","departement_siege","region_siege","lat","lang","nombreHabitant","id_epic"])+['id_collectivite'=>$client->id_collectivite]);
        return response([
            "ok"=>true,
            "data"=> $commune
        ],200);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Commune  $commune
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $this->validate($request,[
            "id_commune"=>["required","exists:communes"],
            "nomCommune"=>["required"],
            "adresse"=>["required"],
            "nombreHabitant"=>["required","numeric"],
            "epic"=>["required","exists:epics,id_epic"],
            "serin"=>["required","numeric","digits:9"],
            "insee"=>["required","numeric","digits:5"]
        ]);
        $commune =Commune::find($request["id_commune"]); 
        $socU = $commune->update($request->only(["nomCommune","adresse","logo","serin","insee","departement_siege","region_siege","lat","lang","nombreHabitant","id_epic"]));
        return response([
            "ok"=>true,
            "data"=>"Commune modifiée avec succée"
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
     * @param  \App\Models\Commune  $commune
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
        if(!empty($request['idcommune'])){
            $commune=Commune::with(['epic','logo'])->find($request['idcommune']);
            return response([
                'ok'=>true,
                'data'=>$commune
            ],200);
        }
        return response([
            'ok'=>'server',
            'errors'=>'Aucune commune disponible'
        ],400);
    }

    

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        if(isset($request['communes']) && is_array($request['communes'])){
            $deletedLis=[];
            foreach($request['communes'] as $commune){
                $communeObj=Commune::find($commune);
                if($communeObj){
                    $deletedLis [] = $commune;
                    $communeObj->delete();
                }
            }
            return response([
                'ok'=>true,
                'data'=>"async",
                'communes'=>$deletedLis
            ]);
        }
        return response([
            'ok'=>true,
            'data'=>"no action"
        ]);
    }
}