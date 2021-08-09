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
    public function index(Request $request)
    {
        $search=$request->get('search');
        $typeJoin=$request->get('typeFilter');
        $nom=$request->get('nom');$nom=$nom?$nom:$search;
        $prenom=$request->get('prenom');$prenom=$prenom?$prenom:$search;
        $email=$request->get('email');$email=$email?$email:$search;
        $phone=$request->get('phone');$phone=$phone?$phone:$search;
        $sort=$request->get('sort');
        $sorter=$request->get('sorter');
        $function='where';
        $pageSize=$request->get('pageSize')?$request->get('pageSize'):20;
        $Query = UserSimple::query();
        $Query=$Query->join('users','users.id','=','user_simples.id_user');
        if($nom){
            $Query=$Query->{$function}("user_simples.nom","ILIKE","%{$nom}%");
            $function=$typeJoin=="inter"?"where":"orWhere";
        }
        if($prenom){
            $Query=$Query->{$function}("user_simples.prenom","ILIKE","%{$prenom}%");
            $function=$typeJoin=="inter"?"where":"orWhere";
        }
        if($email){
            $Query=$Query->{$function}("user_simples.email_user_sim","ILIKE","%{$email}%");
            $function=$typeJoin=="inter"?"where":"orWhere";
        }
        if($phone){
            $Query=$Query->{$function}("user_simples.phone","ILIKE","%{$phone}%");
            $function=$typeJoin=="inter"?"where":"orWhere";
        }
        $Query=$Query->where('users.typeuser','=','UserSimple');
        if(in_array($sort,['ASC','DESC']) && in_array($sorter,['nom','prenom','phone'])){
            $Query=$Query->orderBy("user_simples.".$sorter,$sort);
        }else{
            $Query=$Query->orderBy("user_simples.updated_at","DESC");
        }
        $users=$Query->paginate($pageSize,['users.username','users.init_password','user_simples.email_user_sim AS email','user_simples.nom','user_simples.prenom','user_simples.id_user_simple as id_user','user_simples.phone']);
        return response([
            "ok"=>true,
            "data"=> $users
        ],200);
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
    public function update(Request $request)
    {
        $userSimp=UserSimple::where('id_user_simple',$request->id_user)
            ->first();
        if($userSimp){
            $userAsso=User::find($userSimp->id_user);
            if(isset($request['nom'])){
                $userSimp->nom=$request['nom'];
            }
            if(isset($request['prenom'])){
                $userSimp->prenom=$request['prenom'];
            }
            if(isset($request['phone'])){
                $userSimp->phone=$request['phone'];
            }
            if(isset($request['email']) && !UserSimple::where('email_user_sim','=', $request['email'] )->exists()){
                $userSimp->email_user_sim=$request['email'];
            }
            if(isset($request['username']) && !User::where('username','=', $request['username'] )->exists()){
                $userAsso->username=$request['username'];
            }
            if(!empty($request['init_password']) && $userAsso->init_password){
                $userAsso->init_password=$request['init_password'];
                $userAsso->password=Hash::make($request['init_password']);
            }
            $userSimp->save();
            $userAsso->save();
            return response([
                "ok"=>true,
                "data"=>"user updated"
            ],200);
        }
        return response([
            'ok'=>false,
            'message'=>'You have not more access'
        ],402);
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