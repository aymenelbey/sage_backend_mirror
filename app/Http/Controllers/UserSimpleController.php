<?php

namespace App\Http\Controllers;

use App\Models\UserSimple;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Validator;
use JWTAuth;
use Illuminate\Http\Request;

class UserSimpleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request){
        $message = [
            "firstname.required" => "Nom est obligatoire",
            "lastname.required"=>"Prénom est olbligatoir",
            "password.required"=> "Mot de pass est obligatoire",
            "email_user_sim.email"=>"Email doit être un email valide",
            "username.unique"=>"Nom d'utilisateur doit être unique",
            "username.required"=>"Nom d'utilisateur est obligatoire",
            "email_user_sim.unique"=>"Email doit être unique"
        ];
        $rules = [
            "firstname"=>["required"],
            "lastname"=>["required"],
            "username"=>["required","unique:users"],
            "password"=>["required"],
            "email_user_sim"=>["email","unique:user_simples"],
        ];
        $validator = Validator::make($request->all(),$rules,$message);
        if($validator->fails()){
            return response([
                "ok"=>false,
                "message"=>$validator->errors()
            ],400);
        }
        $user = JWTAuth::user();
        $usersimp = User::create([
            "firstname"=>$request["firstname"],
            "lastname"=>$request["lastname"],
            "username"=> $request["username"],
            "typeuser"=>"UserSimple",
            "password"=>Hash::make($request["password"])
        ]);
        $usSim = UserSimple::create([
            "email_user_sim"=>$request["email_user_sim"],
            "id_user"=>$usersimp->id,
            "created_by"=>$user->id
        ]);
        return response([
            "ok"=>true,
            "message"=>$usersimp
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
     * @param  \App\Models\UserSimple  $userSimple
     * @return \Illuminate\Http\Response
     */
    public function show(UserSimple $userSimple)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UserSimple  $userSimple
     * @return \Illuminate\Http\Response
     */
    public function edit(UserSimple $userSimple)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UserSimple  $userSimple
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserSimple $userSimple)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserSimple  $userSimple
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserSimple $userSimple)
    {
        //
    }
}
