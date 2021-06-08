<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PasswordReset;
use App\Models\Admin;
use App\Models\Gestionnaire;
use App\Models\UserPremieum;
use App\Models\User;
use App\Notifications\ResetPassword as ResetPasswordNotification;
use Notification;



class ForgotPasswordController extends Controller
{
    public function forgot(Request $request) {
        $this->validate($request,[
            'email' => ["required","email"],
            'username' => ["required","exists:users,username"]
        ]);
        $user=User::where('username',$request->username)->first();
        switch($user->typeuser){
            case "Admin":
            case "SupAdmin":
                $usr=Admin::where('email',$request->email)->first();
                break;
            case "Gestionnaire":
                $usr=Gestionnaire::where('email',$request->email)->first();
                break;
            case "UserPremieume":
                $usr=UserPremieum::where('email_user_prem',$request->email)->first();
                break;
        }
        if($usr){
            $token = Str::random(64);
            PasswordReset::where("username",$request->username)->delete();
            $reset=PasswordReset::create([
                'email' => $request->email, 
                'token' => $token, 
                'username'=>$request->username
            ]);
            $detail=[
                'url'=>url(route('password.reset', [
                    'token' => $token,
                    'email' => $request->email,
                    'username'=>$request->username
                ], false)),
                'name'=>$usr->nom
            ];
            Notification::route('mail', $request->email)->notify(new ResetPasswordNotification($detail));
            return response()->json(["message" =>"Email sended succefully"]); 
        }
        return response([
            "message"=>"The given data was invalid.",
            "errors"=>['email'=>"Le champ email sélectionné est invalide."]
        ],401); 
    }
}