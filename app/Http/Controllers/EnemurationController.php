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
        ->orderBy('updated_at','DESC')
       ->get(['id_enemuration AS value', 'value_enum AS label','key_enum', 'code'])
       ->groupBy('key_enum');
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
            ['id_enemuration' =>$idEnum , 'key_enum' => $request["key"]],
            ['value_enum' => $request["value"]]
        );
        return response([
            "ok"=>true,
            "data"=>['label'=>$enum->value_enum,'value'=>$enum->id_enemuration]
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
        $this->validate($request,[
             "enumuration"=>["required","exists:enemurations,id_enemuration"]
        ]);
        try{
            $enum = Enemuration::find($request['enumuration']);
            if($enum){
                if($enum->canDelete()){
                    $enum = Enemuration::destroy($request['enumuration']);
                    return response([
                        "ok"=>true,
                        "data"=>$request['enumuration']
                    ],200);
                }
                throw new \Exception();
            }
        }catch(\Exception $e){
            return response([
                "errors"=>true,
                "message"=>"item already in use"
            ],402);
        }    
    }
}