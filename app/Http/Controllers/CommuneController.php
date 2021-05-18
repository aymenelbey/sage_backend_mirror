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
        $function='where';
        $pageSize=$request->get('pageSize')?$request->get('pageSize'):10;
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
        $commune=$communeQuery->orderBy("created_at","DESC")
        ->paginate($pageSize);
        return response([
            "ok"=>true,
            "data"=>$commune
        ],200);
    }
    public function show(Request $request){
        if(!empty($request['idcommune'])){
            $idcommune=$request['idcommune'];
            $commune=Commune::with('epic')
            ->with('contacts')
            ->find($idcommune);
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
        $validator = Validator::make($request->all(),[
            "id_epic"=>["required","exists:e_p_i_c_s"],
            "id_commune"=>["required","exists:communes"]
        ],[
            "id_epic.required" => "L'EPCI est obligatoire",
            "id_commune.required" => "La Commune est obligatoire",
            "id_commune.exists"=>"La commune doit être existe dans la list",
            "id_epic.exists"=>"L'EPCI doit être existe dans la list"
        ]);
        if($validator->fails()){
            return response([
                "ok"=>false,
                "message"=>$validator->errors()
            ],400);
        }
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
            "nombreHabitant"=>["required","numeric"],
            "epic"=>["required","exists:epics,id_epic"]
        ],[
            "required"=>":attribute est obligatoire",
            "numeric"=>":attribute doit être un nombre",
            "epic.exists"=>"Epic doit être existe" 
        ]);
        $client = Collectivite::create([
            "typeCollectivite"=>"Commune"
        ]);
        $commune = Commune::create([
            "nomCommune"=>$request["nomCommune"],
            "adresse"=>$request["adresse"],
            "lat"=>$request['lat'],
            "lang"=>$request['lang'],
            "nombreHabitant"=>$request["nombreHabitant"],
            "id_epic"=>$request["epic"],
            'id_collectivite'=>$client->id_collectivite
        ]);
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
            "epic"=>["required","exists:epics,id_epic"]
        ],[
            "required"=>":attribute est obligatoire",
            "numeric"=>":attribute doit être un nombre",
            "epic.exists"=>"Epic doit être existe" 
        ]);
        $commune =Commune::find($request["id_commune"]); 
        $socU = $commune->update([
            "nomCommune"=>$request["nomCommune"],
            "adresse"=>$request["adresse"],
            "lat"=>$request['lat'],
            "lang"=>$request['lang'],
            "nombreHabitant"=>$request["nombreHabitant"],
            "id_epic"=>$request["epic"]
        ]);
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
            $commune=Commune::find($request['idcommune']);
            $commune->epic;
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