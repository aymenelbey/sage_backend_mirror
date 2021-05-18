<?php

namespace App\Http\Controllers;

use App\Models\Collectivite;
use App\Models\SocieteExpSite;
use Carbon\Carbon;
use App\Models\ClientExp;
use Illuminate\Http\Request;
use Validator;

class CollectiviteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $collectivite =  Collectivite::with('client')->get();
        return response([
            "ok"=>true,
            "data"=>$collectivite
        ],200);
    }
    public function sitesByClient(Request $request){
        $colle = Collectivite::where("id_collectivite","=",$request["idClient"])->with(["exploit"=>function($query){
            $query->with("sites");
        }])->first();
        if($colle){
            return response([
                "ok"=>true,
                "message"=>$colle
            ],200);
        }
        return response([
            "ok"=>false,
            "message"=>"Aucun client est correspond"
        ],400);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function add(Request $request){
        $validator = Validator::make($request->all(),[
            "id_collectivite"=> ["required","exists:collectivites"],
            "id_sites"=>["required"]
        ],[
            "id_collectivite.exists"=>"Client n'existe pas",
            "id_sites.required"=>"Les sites sont obligatoires",
            "id_collectivite.required"=>"Le client est obligatoire"
        ]);
        if($validator->fails()){
            return response([
                "ok"=>false,
                "message"=>$validator->errors()
            ],400);
        }
        foreach($request["id_sites"] as $id){
            $vald = Validator::make(["id_site"=>$id],["id_site"=>"exists:sites"],["id_site.exists"=>"Le site n'existe pas" ]);
            if($vald->fails()){
                return response([
                    "ok"=>false,
                    "message"=>$vald->errors()
                ],400);
            }
        }
        $array_sites = [];
        foreach($request["id_sites"] as $id){
            $soc = SocieteExpSite::where("id_site","=",$id)->where("typeExploitant","=","Client")->select("id_societe_exp_site")->first();
            if(!$soc){
                $soc = SocieteExpSite::create([
                    "typeExploitant"=>"Client",
                    'id_site'=>$id
                ]);
            }
            $temp = [
                "id_societe_exp_site"=>$soc->id_societe_exp_site,
                "id_collectivite"=>$request["id_collectivite"],
                "created_at"=>Carbon::now(),
                "updated_at"=>Carbon::now()
            ];
            array_push($array_sites,$temp);
        }
        $socExp = ClientExp::insert($array_sites);
        return response([
            "ok"=>true,
            "message"=>$socExp
        ],200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Collectivite  $collectivite
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        return response([
            'data'=>Collectivite::get()
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Collectivite  $collectivite
     * @return \Illuminate\Http\Response
     */
    public function edit(Collectivite $collectivite)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Collectivite  $collectivite
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Collectivite $collectivite)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Collectivite  $collectivite
     * @return \Illuminate\Http\Response
     */
    public function destroy(Collectivite $collectivite)
    {
        //
    }
}