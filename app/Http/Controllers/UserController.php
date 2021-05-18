<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use JWTAuth;

class UserController extends Controller
{
    public function updatePassword(Request $request){
        $this->validate($request,[
            "password"=>['required'],
            "cPassword"=>['required'],
            "currentPassword"=>['required']
        ]);
        $user=JWTAuth::user();
        $passed=Hash::check($request['currentPassword'], $user->password);
        if($passed){
            $passed=$request['cPassword']==$request['password'];
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
                "errors"=>"Le mot de passe et la confirmation du mot de passe ne correspondent pas"
            ],400);
        }
        return response([
            "message"=>"The given data was invalid.",
            "errors"=>"L'ancien mot de passe ne correspond pas"
        ],400);
    }
}