<?php

namespace App\Http\Controllers;

use App\Models\Enemuration;
use Illuminate\Http\Request;
use Validator;

class EnemurationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       $enums=Enemuration::orderBy('created_at', 'desc')
       ->get(['id_enemuration AS value', 'valueEnum AS label','keyEnum'])
       ->groupBy('keyEnum');
       return response([
        "ok"=> true,
        "data"=> $enums
        ],200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request){
        $validator = Validator::make($request->all(),[
            "key"=>["required"],
            "value"=>["required"]
        ],[
            "key.required"=>"Le parametre de l'enumuration est obligatoire",
            "value.required"=>"La valeur de l'enumuration ne peut pas etre vide"
        ]);
        if($validator->fails()){
            return response([
                "ok"=>false,
                "errors"=>$validator->errors()
            ],400);
        }
        $idEnum=$request['enemuration'];
        $enum = Enemuration::updateOrCreate(
            ['id_enemuration' =>$idEnum , 'keyEnum' => $request["key"]],
            ['valueEnum' => $request["value"]]
        );
        return response([
            "ok"=>true,
            "data"=>['label'=>$enum->valueEnum,'value'=>$enum->id_enemuration]
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
     * @param  \App\Models\Enemuration  $enemuration
     * @return \Illuminate\Http\Response
     */
    public function show(Enemuration $enemuration)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Enemuration  $enemuration
     * @return \Illuminate\Http\Response
     */
    public function edit(Enemuration $enemuration)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Enemuration  $enemuration
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Enemuration $enemuration)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Enemuration  $enemuration
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(),[
            "enumuration"=>["required","exists:enemurations,id_enemuration"]
        ],[
            "required"=>"L'enumuration est obligatoire",
            "exists"=>"L'enemuration n'exists pas"
        ]);
        if($validator->fails()){
            return response([
                "ok"=>false,
                "errors"=>$validator->errors()
            ],400);
        }
        $enum = Enemuration::destroy($request['enumuration']);
        return response([
            "ok"=>true,
            "data"=>$request['enumuration']
        ],200);
    }
}