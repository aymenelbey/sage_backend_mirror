<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserPremieum;
use App\Models\UserSimple;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use JWTAuth;

class SessionsUserController extends Controller
{
    public function index(Request $request){
        $user = JWTAuth::user();
        $sessions=UserSimple::join('users','users.id','=','user_simples.id_user')
                ->where('user_simples.created_by',$user->id)
                ->orderBy('user_simples.created_at','ASC')
                ->get(['users.username','users.init_password','user_simples.email_user_sim AS email','user_simples.nom','user_simples.prenom','user_simples.id_user_simple as id_user','user_simples.phone']);
        return response([
            'ok'=>true,
            'sessions'=>$sessions
        ],200);
    }
    public function update(Request $request){
        $this->validate($request,[
            "nom"=>["required"],
            "prenom"=>["required"],
            "email"=>["required","email"],
            'username'=>['required'],
            'phone'=>['required','phone:FR']
        ]);
        $userAuth = JWTAuth::user();
        $userPrem=UserPremieum::where("id_user",$userAuth->id)
        ->first();
        $userSimp=UserSimple::where('id_user_simple',$request->id_user)
            ->where('created_by',$userAuth->id)
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
                "session"=>"user updated"
            ],200);
        }
        return response([
            'ok'=>false,
            'message'=>'You have not more access'
        ],402);
    }

    public function create(Request $request){
        $userAuth = JWTAuth::user();
        $userPrem=UserPremieum::where("id_user",$userAuth->id)
        ->first();
        if($userPrem->NbUserCreated<$userPrem->nbAccess){
            $this->validate($request,[
                "nom"=>["required"],
                "prenom"=>["required"],
                "email"=>["required","email","unique:user_simples,email_user_sim"],
                'username'=>['nullable','unique:users,username'],
                'phone'=>['nullable','phone:FR']
            ]);
            $password=$request->init_password ?? Str::random(12);
            $user=User::create([
                'username'=>$request->username,
                'password'=>Hash::make($password),
                'init_password'=>$password,
                "typeuser"=>'UserSimple',
            ]);
            $userSimple=UserSimple::create([
                'email_user_sim'=>$request->email,
                'nom'=>$request->nom,
                'prenom'=>$request->prenom,
                'id_user'=>$user->id,
                'created_by'=>$userAuth->id,
                'phone'=>$request->phone
            ]);
            $userPrem->NbUserCreated+=1;
            $userPrem->save();
            return response([
                'ok'=>true,
                'session'=>'Sessions created '
            ],200);
        }
        return response([
            'ok'=>false,
            'message'=>'You have not more access'
        ],402);
        
    }
}