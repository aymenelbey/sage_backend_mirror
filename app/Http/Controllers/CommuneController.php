<?php

namespace App\Http\Controllers;

use App\Models\Commune;
use App\Models\Collectivite;
use App\Models\InfoClientHistory;
use Illuminate\Http\Request;
use App\Http\Helpers\SiteHelper;
use Validator;
use App\Rules\Siren;
use Carbon\Carbon;

class CommuneController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request){
        $search=$request->get('search');
        $typeJoin=$request->get('typeFilter');
        $nomCommune=$request->get('nomCommune');$nomCommune=$nomCommune?$nomCommune:$search;
        $address=$request->get('address');$address=$address?$address:$search;
        $serin=$request->get('serin');$serin=$serin?$serin:$search;
        $insee=$request->get('insee');$insee=$insee?$insee:$search;
        $nombreHabitant=$request->get('nombreHabitant');
        $id_commune=$request->get('id_commune');
        $requireList=$request->get('list');
        $sort=$request->get('sort');
        $sorter=$request->get('sorter');
        $function='where';
        $pageSize=$request->get('pageSize')?$request->get('pageSize'):20;
        $communeQuery = Commune::query();
        if($id_commune){
            $communeQuery=$communeQuery->{$function}("id_commune","=",$id_commune);
            $function=$typeJoin=="inter"?"where":"orWhere";
        }
        if($nomCommune){
            $communeQuery=$communeQuery->{$function}("nomCommune","ILIKE","%{$nomCommune}%");
            $function=$typeJoin=="inter"?"where":"orWhere";
        }
        if($nombreHabitant){
            $communeQuery=$communeQuery->{$function}("nombreHabitant","<=",$nombreHabitant);
            $function=$typeJoin=="inter"?"where":"orWhere";
        }
        if($serin){
            $communeQuery=$communeQuery->{$function}("serin","ILIKE","%{$serin}%");
            $function=$typeJoin=="inter"?"where":"orWhere";
        }
        if($insee){
            $communeQuery=$communeQuery->{$function}("insee","ILIKE","%{$insee}%");
            $function=$typeJoin=="inter"?"where":"orWhere";
        }
        if($address){
            $communeQuery=$communeQuery->{$function}("adresse","ILIKE","%{$address}%");
            $function=$typeJoin=="inter"?"where":"orWhere";
        }
        if($requireList && ($nomCommune || $address)){
            $arrayData=explode(".",$requireList);
            $communeQuery=$communeQuery->{$function."In"}("id_commune",$arrayData);
        }
        if(in_array($sort,['ASC','DESC']) && in_array($sorter,["nomCommune","adresse","insee","serin","departement_siege","region_siege","nombreHabitant","id_commune"])){
            $communeQuery=$communeQuery->orderBy($sorter,$sort);
        }else{
           $communeQuery=$communeQuery->orderBy("updated_at","DESC");
        }
        $communes=$communeQuery->paginate($pageSize);
        return response([
            "ok"=>true,
            "data"=>$communes
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
            "city"=>["required"],
            "country"=>['required'],
            "postcode"=>['required'],
            "serin"=> ["required","numeric", new Siren],
            "insee"=>["required","numeric","digits:5"],
            "nombreHabitant"=>["required","numeric"],
            'departement_siege'=>["required","exists:enemurations,id_enemuration"],
            'region_siege'=>["required","exists:enemurations,id_enemuration"],
            "id_epic"=>["required","exists:epics,id_epic"]
        ],[],[
            'serin'=>'Siren',
            'id_epic'=>"EPCI de rattachement"
        ]);
        $client = Collectivite::create([
            "typeCollectivite"=>"Commune"
        ]);
        $commune = Commune::create($request->only(["nomCommune","adresse","logo","serin","insee","departement_siege","region_siege","lat","lang","nombreHabitant","id_epic","city","country","postcode"])+['id_collectivite'=>$client->id_collectivite,'date_enter'=>Carbon::now()]);
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
            "id_epic"=>["required","exists:epics,id_epic"],
            'departement_siege'=>["required","exists:enemurations,id_enemuration"],
            'region_siege'=>["required","exists:enemurations,id_enemuration"],
            "serin"=> ["required","numeric", new Siren],
            "insee"=>["required","numeric","digits:5"]
        ],[],[
            'serin'=>'Siren'
        ]);
        $commune =Commune::find($request["id_commune"]);
        $moreItems=[];
        if($commune->nombreHabitant!=$request['nombreHabitant']){
            $moreItems=[
                'nombreHabitant'=>$request['nombreHabitant'],
                'date_enter'=>Carbon::now()
            ];
            InfoClientHistory::create([
                'id_reference'=>$commune->id_commune,
                'referenced_table'=>"Commune",
                'referenced_column'=>'nombreHabitant',
                'date_reference'=>$commune->date_enter,
                'prev_value'=>$commune->nombreHabitant
            ]);
        }
        $moreItems=[
            'logo'=>isset($request['logo'])?$request['logo']:null
        ];
        $commune->update($request->only(["nomCommune","adresse","serin","insee","departement_siege","region_siege","lat","lang","city","country","postcode","id_epic"])+$moreItems);
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
            $commune=Commune::with(['epic','logo','departement_siege:id_departement,id_departement AS value,name_departement AS label','region_siege:id_region,id_region AS value,name_region AS label'])->find($request['idcommune']);
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