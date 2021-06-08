<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Validator;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $nom=$request->get('nom');
        $prenom=$request->get('prenom');
        $function='where';
        $pageSize=$request->get('pageSize')?$request->get('pageSize'):10;
        $adminQuery = Admin::query();
        $adminQuery=$adminQuery->join("users","admins.id_user","=","users.id");
        if($nom){
            $adminQuery=$adminQuery->{$function}("admins.nom","ILIKE","%{$nom}%");
            $function='orWhere';
        }
        if($prenom){
            $adminQuery=$adminQuery->{$function}("admins.prenom","ILIKE","%{$prenom}%");
            $function='orWhere';
        }
        $adminQuery=$adminQuery->where('users.typeuser','=','Admin');
        $admins=$adminQuery->orderBy("admins.updated_at","DESC")
        ->paginate($pageSize,['admins.id_admin','users.init_password','admins.nom','admins.prenom','admins.email AS email_admin','users.username','admins.phone']);
        return response([
            "ok"=>true,
            "data"=> $admins
        ],200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $this->validate($request,[
            "nom"=>["required"],
            "prenom"=>["required"],
            "email"=>["required","email","unique:admins,email"]
        ]);
        $username=User::getUsername($request['nom'],$request['prenom']);
        $password=Str::random(12);
        $user =  User::create([
            "username"=>$username,
            "typeuser"=>"Admin",
            "password"=>Hash::make($password),
            "init_password"=>$password
        ]);
        $admin = Admin::create([
            "id_user"=>$user->id,
            "nom"=>$request['nom'],
            "prenom"=>$request['prenom'],
            "phone"=>isset($request['phone'])?$request['phone']:null,
            "email"=>$request['email']
        ]);
        return response([
            "ok"=>true,
            "data"=> $admin
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
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Http\Response
     */
    public function show(Admin $admin)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Http\Response
     */
    public function edit(Admin $admin)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $idAdmin=isset($request['id_admin'])?$request['id_admin']:null;
        if($idAdmin){
            $admin=Admin::find($idAdmin);
            $user=User::find($admin->id_user);
            if(isset($request['nom'])){
                $admin->nom=$request['nom'];
            }
            if(isset($request['prenom'])){
                $admin->prenom=$request['prenom'];
            }
            if(isset($request['phone'])){
                $admin->phone=$request['phone'];
            }
            if(isset($request['email_admin']) && !Admin::where('email','=', $request['email_admin'] )->exists()){
                $admin->email=$request['email_admin'];
            }
            if(isset($request['username']) && !User::where('username','=', $request['username'] )->exists()){
                $user->username=$request['username'];
            }
            if(!empty($request['init_password']) && $user->init_password){
                $user->init_password=$request['init_password'];
                $user->password=Hash::make($request['init_password']);
            }
            $admin->save();
            $user->save();
            return response([
                "ok"=>true,
                "data"=>"user updated"
            ],200);
        }
        return response([
            "message"=>'The given data was invalid.',
            "errors"=>"admin is required"
        ],400);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        if(isset($request['admins']) && is_array($request['admins'])){
            $deletedLis=[];
            foreach($request['admins'] as $admin){
                $admin=Admin::find($admin);
                if($admin){
                    $user=User::find($admin->id_user);
                    $deletedLis [] = $admin->id_admin;
                    $admin->delete();
                    $user->delete();
                }
            }
            return response([
                'ok'=>true,
                'data'=>"async",
                'admins'=>$deletedLis
            ],200);
        }
        return response([
            'ok'=>true,
            'data'=>"no action"
        ],200);
    }
}