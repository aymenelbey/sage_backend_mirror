<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use JWTAuth;

class UserController extends Controller
{
    CONST USER_ATTRIBUTES=[
        'Admin'=>[
            'nom'=>'nom',
            'prenom'=>'prenom',
            'phone'=>'phone',
            'email'=>'email',
            'className'=>'App\Models\Admin'
        ],
        'Gestionnaire'=>[
            'nom'=>'nom',
            'prenom'=>'prenom',
            'phone'=>'mobile',
            'email'=>'email',
            'className'=>'App\Models\Gestionnaire'
        ],
        'UserPremieume'=>[
            'nom'=>'nom',
            'prenom'=>'prenom',
            'phone'=>'phone',
            'email'=>'email_user_prem',
            'className'=>'App\Models\UserPremieum'
        ],
        'UserSimple'=>[
            'nom'=>'nom',
            'prenom'=>'prenom',
            'phone'=>'phone',
            'email'=>'email_user_sim',
            'className'=>'App\Models\UserSimple'
        ]
    ];
    public function updatePassword(Request $request){
        $this->validate($request,[
            "password"=>['required','same:cPassword','min:6'],
            "cPassword"=>['required'],
            "currentPassword"=>['required']
        ],[],[
            'cPassword'=>"Mot de pass de confirmation",
            "password"=>"Mot de pass"
        ]);
        $user=JWTAuth::user();
        $passed=Hash::check($request['currentPassword'], $user->password);
        if($passed){
            $user->password=Hash::make($request['password']);
            $user->init_password=null;
            $user->save();
            return response([
                "ok"=>true,
                "data"=>"Password updated"
            ]);
        }
        return response([
            "message"=>"The given data was invalid.",
            "errors"=>[
                "currentPassword"=>"L'ancien mot de passe ne correspond pas"
            ]
        ],400);
    }
    public function updateUser(Request $request){
        $this->validate($request,[
            "nom"=>["required"],
            "prenom"=>["required"],
            "email"=>["required","email"],
            'username'=>['required'],
            'phone'=>['required','phone:FR']
        ]);
        $user = JWTAuth::user();
        $userAssociat=$user->userType;
        if(isset($request['nom'])){
            $userAssociat->{self::USER_ATTRIBUTES[$user->typeuser]['nom']}=$request['nom'];
        }
        if(isset($request['prenom'])){
            $userAssociat->{self::USER_ATTRIBUTES[$user->typeuser]['prenom']}=$request['prenom'];
        }
        if(isset($request['phone'])){
            $userAssociat->{self::USER_ATTRIBUTES[$user->typeuser]['phone']}=$request['phone'];
        }
        if(isset($request['email']) && !self::USER_ATTRIBUTES[$user->typeuser]['className']::where(self::USER_ATTRIBUTES[$user->typeuser]['email'],'=', $request['email'] )->exists()){
            $userAssociat->{self::USER_ATTRIBUTES[$user->typeuser]['email']}=$request['email'];
        }
        if(isset($request['username']) && !User::where('username','=', $request['username'] )->exists()){
            $user->username=$request['username'];
        }
        $user->save();
        $userAssociat->save();
        return response([
            'ok'=>true,
            'data'=>'async'
        ],200);
    }
    public function updatePicture(Request $request){
        $user = JWTAuth::user();
        $path=$request->file('file')->store('images');
        $user->picture=$path;
        $user->save();
        return response([
            'ok'=>true,
            'picture'=>asset($path)
        ],200);
    }
}