<?php

namespace App\Http\Controllers;

use App\Models\EPIC;
use App\Models\Collectivite;
use App\Models\SyndicatHasEpic;
use Illuminate\Http\Request;
use Validator;
use Carbon\Carbon;

class EPICController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request)
    {
        $nomEpic=$request->get('nomepic');
        $address=$request->get('address');
        $function='where';
        $pageSize=$request->get('pageSize')?$request->get('pageSize'):10;
        $epicQuery = EPIC::query();
        if($nomEpic){
            $epicQuery=$epicQuery->{$function}("nomEpic","ILIKE","%{$nomEpic}%");
            $function='orWhere';
        }
        if($address){
            $epicQuery=$epicQuery->{$function}("adresse","ILIKE","%{$address}%");
            $function='orWhere';
        }
        $epics=$epicQuery->orderBy("created_at","DESC")
        ->paginate($pageSize);
        return response([
            "ok"=>true,
            "data"=> $epics
        ],200);
    }
    public function showWithCommune(Request $request){
        $epic = EPIC::where("id_epic","=",$request["id"])->with("communes")->first();
        if($epic){
            return response([
                "ok"=>true,
                "message"=>$epic
            ],200);
        }
        return response([
            "ok"=>true,
            "message"=>"Epic n'exite pas"
        ],400);
    }
    public function showWithSyndicat(Request $request){
        $epic = EPIC::where("id_epic","=",$request["id"])->with("syndicats")->first();
        if($epic){
            return response([
                "ok"=>true,
                "message"=>$epic
            ],200);
        }
        return response([
            "ok"=>true,
            "message"=>"Epic n'exite pas"
        ],400);

    }
    public function updateCommune(Request $request){
        $rules = [
            "id_epic"=>["required","exists:e_p_i_c_s"],
            "id_communes"=>["required"]
        ];
        $validator = Validator::make($request->all(),$rules);
        if($validator->fails()){
            return response([
                "ok"=>false,
                "message"=>$validator->errors()
            ],400);
        }
        //$array_communes = [];
        foreach($request["id_communes"] as $id){
            $val = Validator::make(["id_commune"=>$id],["id_commune"=>"exists:communes"],["exists"=>"Commune n'existe pas"]);
            if($val->fails()){
                return response([
                    "ok"=>false,
                    "message"=>$val->errors()
                ],400);
            }
        }
        foreach($request["id_communes"] as $id){
            $upd =  Commune::where("id_commune","=",$id)->update(["id_epic"=>$request["id_epic"]]);
        }
        return response([
            "ok"=>true,
            "message"=>"Les communes sont associés avec succés"
        ],200);
    }
    public function updateSyndicat(Request $request){
        $rules = [
            "id_epic"=>["required","exists:e_p_i_c_s"],
            "id_syndicat"=>["required"]
        ];
        $validator = Validator::make($request->all(),$rules);
        if($validator->fails()){
            return response([
                "ok"=>false,
                "message"=>$validator->errors()
            ],400);
        }
        $array_syndicat = [];
        foreach($request["id_syndicat"] as $id){
            $val = Validator::make(["id_syndicat"=>$id],["id_syndicat"=>"exists:syndicats"],["exists"=>"Syndicat n'existe pas"]);
            if($val->fails()){
                return response([
                    "ok"=>false,
                    "message"=>$val->errors()
                ],400);
            }
            $temp = [
                "id_epic"=>$id,
                "id_syndicat"=>$id,
                "created_at"=>Carbon::now(),
                "updated_at"=>Carbon::now()
            ];
            array_push($array_syndicat,$temp);
        }
        $syndic = SyndicatHasEpic::insert($array_syndica);
        return response([
            "ok"=>true,
            "message"=>"Données insérées avec succées"
        ],200);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $this->validate($request,[
            "nomEpic"=>["required","string"],
            'nom_court'=>["required"],
            "sinoe"=>['required'],
            "serin"=>["required","numeric","digits:9"],
            'nature_juridique'=>["required","exists:enemurations,id_enemuration"],
            'departement_siege'=>["required","exists:enemurations,id_enemuration"],
            'competence_dechet'=>["required","exists:enemurations,id_enemuration"],
            'region_siege'=>["required","exists:enemurations,id_enemuration"], 
        ]);
        $client = Collectivite::create([
            "typeCollectivite"=>"EPIC"
        ]);
        $epic = EPIC::create($request->only(["nomEpic","serin","nom_court","sinoe","adresse","lat","lang","siteInternet","telephoneStandard","nombreHabitant","logo","nature_juridique","departement_siege","competence_dechet","region_siege","exerciceCompetance"])+['id_collectivite'=>$client->id_collectivite]);
        if(isset($request['id_syndicat']) && $request['exerciceCompetance']=="déléguée"){
            $syndhas = SyndicatHasEpic::create([
                "id_epic"=>$epic->id_epic,
                "id_syndicat"=>$request['id_syndicat']
            ]);
        }
        return response([
            "ok"=>true,
            "data"=>$epic
        ],200);
        
    }
    /** 
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\EPIC  $ePIC
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $this->validate($request,[
            "id_epic"=>["required","exists:epics"],
            "nomEpic"=>["required","string"],
            "sinoe"=>['required'],
            "serin"=>["required","numeric","digits:9"],
            'nom_court'=>["required"],
            'nature_juridique'=>["required","exists:enemurations,id_enemuration"],
            'departement_siege'=>["required","exists:enemurations,id_enemuration"],
            'competence_dechet'=>["required","exists:enemurations,id_enemuration"],
            'region_siege'=>["required","exists:enemurations,id_enemuration"]
        ]);
        $reqeustClt=collect($request);
        $epic = EPIC::find($request["id_epic"]);
        $prevRattach=SyndicatHasEpic::where("id_epic",$request["id_epic"])->first();
        $CreateNew=(!$prevRattach || $prevRattach->id_syndicat!=$request['id_syndicat']) && $request['exerciceCompetance']=="déléguée";
        if($prevRattach){
            if($request['exerciceCompetance']!="déléguée" || $prevRattach->id_syndicat!=$request['id_syndicat']){
                $prevRattach->delete();
            }
        }
        $epic->update($reqeustClt->only(["nomEpic","nom_court","sinoe","serin","adresse","lat","lang","siteInternet","telephoneStandard","nombreHabitant","logo","nature_juridique","departement_siege","competence_dechet","region_siege","exerciceCompetance"])->toArray());
        if($CreateNew){
            $syndhas = SyndicatHasEpic::create([
                "id_epic"=>$epic->id_epic,
                "id_syndicat"=>$request['id_syndicat']
            ]);
        }
        return response([
            "ok"=>true,
            "data"=>$epic
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
     * @param  \App\Models\EPIC  $ePIC
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        if(!empty($request['idepic'])){
            $idEpic=$request['idepic'];
            $epic=EPIC::with(['communes','syndicat','contacts','logo'])
            ->find($idEpic);
            $epic->withEnums();
            $epic=$epic->toArray();
            if(!empty($epic["logo"][0])){
                $epic["logo"]=$epic["logo"][0]["url"];
            }
            return response([
                'ok'=>true,
                'data'=>$epic
            ],200);
        }
        return response([
            'ok'=>'server',
            'errors'=>'Aucune epic disponible'
        ],400);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\EPIC  $ePIC
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        if(!empty($request['idepic'])){
            $idEpic=$request['idepic'];
            $epic=EPIC::with(['syndicat','logo'])->find($idEpic);
            return response([
                'ok'=>true,
                'data'=>$epic
            ],200);
        }
        return response([
            'ok'=>'server',
            'errors'=>'Aucune epic disponible'
        ],400);
    }

    

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\EPIC  $ePIC
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        if(isset($request['epics']) && is_array($request['epics'])){
            $deletedLis=[];
            foreach($request['epics'] as $epic){
                $epicObj=EPIC::find($epic);
                $collect=Collectivite::find($epicObj->id_collectivite);
                if($epicObj && $collect){
                    $deletedLis [] = $epic;
                    $collect->delete();
                    $epicObj->delete();
                }
            }
            return response([
                'ok'=>true,
                'data'=>"async",
                'epics'=>$deletedLis
            ]);
        }
        return response([
            'ok'=>true,
            'data'=>"no action"
        ]);
    }
}