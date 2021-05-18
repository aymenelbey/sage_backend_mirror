<?php
namespace App\Http\Controllers\auth;

use JWTAuth;
use App\Models\User;
use App\Models\Admin;
use App\Models\Gestionnaire;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator;


class LoginController extends Controller{
    public function login(Request $request){
        $validator = Validator::make($request->all(),[
            "password" => 'required',
            "username"=> ['required']
            
        ],$messages = ['required' => 'The :attribute field is required.']);
        if ($validator->fails()) {
            return response([
                "status"=> "error",
                "message"=>$validator->errors()
            ],400);        
        }
        $input = $request->only(["username","password"]);
        $role=User::whereUsername($request->username)
        ->select("typeuser")
        ->first();
        if($role){
            switch($role->typeuser){
                case "Gestionnaire":
                    JWTAuth::factory()->setTTL(60*24*3);
                    $typeuser = "Gestionnaire";
                    break;
                case "Admin":
                case "SupAdmin":
                    JWTAuth::factory()->setTTL(60*24*365);
                    $typeuser = "Admin";
                    break;
                case "UserPremieume":
                    JWTAuth::factory()->setTTL(60*24*3);
                    $typeuser = "UserPremieume";
                    break;
                case "UserSimple":
                    $typeuser = "UserSimple";
                    JWTAuth::factory()->setTTL(60*24*3);
                    break;
            }
            $token = JWTAuth::attempt($input);
            return  response([
                "ok"=>true,
                "token"=>$token,
                "update"=>$token && JWTAuth::user()->initpassword,
                "typeuser"=>$token?$role->typeuser:"unknown"
            ],$token?200:400);
        }
        return  response([
            "status"=>"fail",
            "message"=>"user not exists"
        ],400);
    }
    public function createAdmin(Request $request ){
        $user = User::create([
            "username"=>"zizoSup",
            "typeuser"=>"SupAdmin",
            "password"=>Hash::make("123456789")
        ]);
        $admin = Admin::create([
            "id_user"=>$user->id,
            "nom"=>"zino",
            "prenom"=>"nino",
            "email"=>"itachibatna@gmail.com"
        ]);
        return response($user);
    }
    public function user(Request $request ){
        $user=JWTAuth::user()->toArray();
        switch($user['typeuser']){
            case "SupAdmin":
            case "Admin":
                $admin=Admin::where("id_user",$user['id'])->first(['nom','prenom','phone','email_admin'])->toArray();
                $user+=$admin;
                break;
            case "Gestionnaire":
                $gest=Gestionnaire::where("id_user",$user['id'])->first(['nom','prenom','telephone1','email'])->toArray();
                $user+=$gest;     
                break; 
        }
        return response([
            "ok"=>true,
            "user"=>$user
        ],200);
    }
}


?>