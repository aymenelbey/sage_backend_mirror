<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\PasswordReset;
use App\Models\Admin;
use App\Models\Gestionnaire;
use App\Models\UserPremieum;
use App\Models\User;
use Hash;

class ResetPasswordController extends Controller
{
    public function index(Request $request){
        $tokenData = PasswordReset::where('token',$request->token)->where('reset_used',false)->first();
        if(!$tokenData || $this->isExpired($tokenData->created_at)){
            return abort(404);
        }
        return view("auth.passwords.reset",[
            "token"=>$request['token'],
            'email'=>urldecode($request->get('email')),
            'username'=>urldecode($request->get("username"))
        ]);
    }
    public function reset_password(Request $request){
        $this->validate($request,[
            'email'=>['required','email','exists:password_resets,email'],
            'username'=>['required','exists:users,username','exists:password_resets,username'],
            'token'=>['required','exists:password_resets,token'],
            'password'=>['required','min:6','confirmed']
        ]);
        $tokenData = PasswordReset::where('token', $request->token)->first();
        if(!$this->isExpired($tokenData->created_at)){
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
                $user->password=Hash::make($request->password);
                $user->save();
                $tokenData->reset_used=true;
                $tokenData->deleted_at=Carbon::now();
                $tokenData->save();
                return redirect(env('CLIENT_URL').'/login');
            }
        }
        return abort(404);
    }
    protected function isExpired($date){
        return Carbon::parse($date)->addSeconds(config('auth.passwords.expire')*60)->isPast();
    }
}