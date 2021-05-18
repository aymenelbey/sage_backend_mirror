<?php

namespace App\Http\Controllers;

use App\Models\Shared;
use App\Models\Admin;
use App\Models\DBShared;
use Illuminate\Http\Request;
use Validator;
use JWTAuth;

class SharedController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function byUserPrem(Request $request){
        $share = Shared::where("id_user_premieum","=",$request["idUserPrem"])->with("dbshared")->get();
        if($share){
            return response([
                "ok"=>true,
                "message"=>$share
            ],200);
        }
        return response([
            "ok"=>false,
            "message"=>"Aucun base est partagée"
        ],400);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function share(Request $request){
        $rules = [
            "id_site"=>["exists:sites"],
            "id_user_premieum"=>["exists:user_premieums"],
            "nomTable"=>["required"],
            "listColumnName"=>["required"],
            "duree"=>["required"]
        ];
        $message = [
            "id_site.exists"=>"Site n'existe pas",
            "id_user_premieum.exists"=>"L'utilisateur premieums n'existe pas ",
            "nomTable.required"=>"Nom de la table est obligatoire",
            "listColumnName.required"=>"La list des colunnes est obligatoire",
            "duree"=>"La durée du partage est obligatoire"
        ];
        $validator =  Validator::make($request->all(),$rules,$message);
        if($validator->fails()){
            return  response([
                "ok"=>false,
                "message"=>$validator->errors()
            ],400);
        }
        $user = JWTAuth::user();
        $admin = Admin::where("id_user","=",$user->id)->first();
        $share = Shared::create([
            "id_admin"=>$admin->id_admin,
            "id_site"=>$request["id_site"],
            "id_user_premieum"=>$request["id_user_premieum"],
            "duree"=>$request["duree"]
        ]);
        $column  = join(";",$request["listColumnName"]);
        $db = DBShared::create([
            "id_shared"=>$share->id_shared,
            "nomTable"=>$request["nomTable"],
            "columnName"=>$column
        ]);
        return response([
            "ok"=>true,
            "message"=>["shared"=>$share,"db_shared"=>$db]
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
     * @param  \App\Models\Shared  $shared
     * @return \Illuminate\Http\Response
     */
    public function show(Shared $shared)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Shared  $shared
     * @return \Illuminate\Http\Response
     */
    public function edit(Shared $shared)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Shared  $shared
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Shared $shared)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Shared  $shared
     * @return \Illuminate\Http\Response
     */
    public function destroy(Shared $shared)
    {
        //
    }
}
