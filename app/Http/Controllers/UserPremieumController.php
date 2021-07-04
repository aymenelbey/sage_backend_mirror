<?php

namespace App\Http\Controllers;

use App\Models\UserPremieum;
use App\Models\User;
use App\Models\Admin;
use App\Models\ShareSite;
use App\Models\UserSimple;
use Illuminate\Support\Str;
use App\Models\UserPremieumHasClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Validator;
use JWTAuth;

class UserPremieumController extends Controller
{
    const PERSONS_DATA=[
        "syndicat"=>[
            "typePersonMoral"=>"Syndicat",
            "name"=>"Nom Court",
            "dataIndex"=>"nomCourt"
        ],
        "epic"=>[
            "typePersonMoral"=>"Epic",
            "name"=>"Nom EPIC",
            "dataIndex"=>"nomEpic"
        ],
        "commune"=>[
            "typePersonMoral"=>"Commune",
            "name"=>"Nom Commune",
            "dataIndex"=>"nomCommune"
        ],
        "societe"=>[
            "typePersonMoral"=>"societe",
            "name"=>"Groupe",
            "dataIndex"=>"groupe"
        ]
        ];
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request){
        $search=$request->get('search');
        $typeJoin=$request->get('typeFilter');
        $nom=$request->get('nom');$nom=$nom?$nom:$search;
        $prenom=$request->get('prenom');$prenom=$prenom?$prenom:$search;
        $nbAccess=$request->get('nbAccess');
        $email=$request->get('email');$email=$email?$email:$search;
        $phone=$request->get('phone');$phone=$phone?$phone:$search;
        $sort=$request->get('sort');
        $sorter=$request->get('sorter');
        $function='where';
        $pageSize=$request->get('pageSize')?$request->get('pageSize'):20;
        $Query = UserPremieum::query();
        $Query=$Query->join("users","user_premieums.id_user","=","users.id");
        if($nom){
            $Query=$Query->{$function}("user_premieums.nom","ILIKE","%{$nom}%");
            $function=$typeJoin=="inter"?"where":"orWhere";
        }
        if($prenom){
            $Query=$Query->{$function}("user_premieums.prenom","ILIKE","%{$prenom}%");
            $function=$typeJoin=="inter"?"where":"orWhere";
        }
        if($email){
            $Query=$Query->{$function}("user_premieums.email_user_prem","ILIKE","%{$email}%");
            $function=$typeJoin=="inter"?"where":"orWhere";
        }
        if($phone){
            $Query=$Query->{$function}("user_premieums.phone","ILIKE","%{$phone}%");
            $function=$typeJoin=="inter"?"where":"orWhere";
        }
        if($nbAccess){
            $Query=$Query->{$function}("user_premieums.nbAccess","<=",$nbAccess);
            $function=$typeJoin=="inter"?"where":"orWhere";
        }
        $Query=$Query->where('users.typeuser','=','UserPremieume');
        if(in_array($sort,['ASC','DESC']) && in_array($sorter,['nom','prenom','phone','nbAccess'])){
            $Query=$Query->orderBy("user_premieums.".$sorter,$sort);
        }else{
            $Query=$Query->orderBy("user_premieums.updated_at","DESC");
        }
        $users=$Query->paginate($pageSize,['user_premieums.id_user_premieum AS id_user','users.init_password','user_premieums.nom','user_premieums.prenom','user_premieums.email_user_prem AS email','users.username','user_premieums.phone','user_premieums.nbAccess']);
        return response([
            "ok"=>true,
            "data"=> $users
        ],200);
    }
    /**
     * Display the spicify resource.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request){
        $prem = $this->getUserById($request["idUser"]);
        if($prem){
            return response([
                "ok"=>true,
                "data"=>$prem
            ],200);
        }
        return response([
            "ok"=>"server",
            "data"=>"Utilisateur n'existe pas"
        ],400);
    }
    private function getUserById($iduser){
        $prem = UserPremieum::where("id_user_premieum","=",$iduser)
        ->join("users","user_premieums.id_user","=","users.id")
        ->first(['users.username','users.init_password','user_premieums.email_user_prem AS email','user_premieums.isPaid','user_premieums.nom','user_premieums.prenom','user_premieums.lastPaiment','user_premieums.phone','user_premieums.NbUserCreated','user_premieums.nbAccess','user_premieums.id_user_premieum AS id_user'])
        ->toArray();
        if($prem){
            $client=UserPremieumHasClient::with('client')
            ->where("id_user_premieum",$iduser)
            ->first();
            if($client){
                $prem['client']=$client->client->toArray();
                $prem['client']+=self::PERSONS_DATA[strtolower($client->typeClient)];
            }
        }
        return $prem;
    }
    /**
     * Show the form for creating a new resource.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request){
        $rules = [
            "nom"=>["required"],
            "client"=>['required'],
            "prenom"=>["required"],
            "email"=>["email","unique:user_premieums,email_user_prem"],
            "nbSession"=>["required","numeric"],
            'phone'=>['nullable','phone:FR']
        ];
        $validator = Validator::make($request->all(),$rules);
        if($validator->fails()){
            return response([
                "ok"=>"server",
                "errors"=>$validator->errors()
            ],400);
        }
        $admin = JWTAuth::user();
        $admin = Admin::where("id_user","=",$admin->id)->select("id_admin")->first();
        $username=User::getUsername($request['nom'],$request['prenom']);
        $password=Str::random(12);
        $user =  User::create([
            "username"=>$username,
            "typeuser"=>"UserPremieume",
            "password"=>Hash::make($password),
            "init_password"=>$password
        ]);
        $prem = UserPremieum::create([
            "email_user_prem"=>$request["email"],
            "nom"=>$request['nom'],
            "prenom"=>$request["prenom"],
            "isPaid"=>true,
            "phone"=>$request['phone'],
            "lastPaiment"=>Carbon::now(),
            "nbAccess"=>$request["nbSession"],
            "created_by"=>$admin->id_admin,
            "id_user"=>$user->id
        ]);
        $idClient="";$typeClient="";
        switch($request['client']['typePersonMoral']){
            case "Epic":
                $typeClient="Epic";
                $idClient=$request['client']['id_epic'];
                break;
            case "Syndicat":
                $typeClient="Syndicat";
                $idClient=$request['client']['id_syndicat'];
                break;
            case "Commune":
                $typeClient="Commune";
                $idClient=$request['client']['id_commune'];
                break;
            case "Societe":
                $typeClient="Societe";
                $idClient=$request['client']['id_societe_exploitant'];
                break;
        }
        $attach=UserPremieumHasClient::create([
            "typeClient"=>$typeClient,
            "id_client"=>$idClient,
            "id_user_premieum"=>$prem->id_user_premieum
        ]);
        return response([
            "ok"=>true,
            "data"=>$prem
        ],200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UserPremieum  $userPremieum
     * @return \Illuminate\Http\Response
     */
    public function edit(UserPremieum $userPremieum)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $this->validate($request,[
            "id_user"=>['required',"exists:user_premieums,id_user_premieum"],
            "nom"=>["required"],
            "prenom"=>["required"],
            "email"=>["email"],
            "nbAccess"=>["required","numeric"],
            'phone'=>['nullable','phone:FR']
        ]);
        $userPrem=UserPremieum::find($request['id_user']);
        $user=User::find($userPrem->id_user);
        $userPrem->nom=$request['nom'];
        $userPrem->prenom=$request['prenom'];
        $userPrem->nbAccess=$request['nbAccess'];
        $userPrem->phone=$request['phone'];
        if($userPrem->email_user_prem!=$request['email'] && !UserPremieum::where('email_user_prem', $request['email'] )->exists()){
            $userPrem->email_user_prem=$request['email'];
        }
        if($user->username!=$request['username'] && !User::where('username', $request['username'] )->exists()){
            $user->username=$request['username'];
        }
        if($user->init_password){
            $user->password=Hash::make($request['init_password']);
            $user->init_password=$request['init_password'];
        }
        if(!empty($request['client'])){
            $idClient="";$typeClient="";
            switch($request['client']['typePersonMoral']){
                case "Epic":
                    $typeClient="Epic";
                    $idClient=$request['client']['id_epic'];
                    break;
                case "Syndicat":
                    $typeClient="Syndicat";
                    $idClient=$request['client']['id_syndicat'];
                    break;
                case "Commune":
                    $typeClient="Commune";
                    $idClient=$request['client']['id_commune'];
                    break;
                case "Societe":
                    $typeClient="Societe";
                    $idClient=$request['client']['id_societe_exploitant'];
                    break;
            }
            $clientUserPrem=UserPremieumHasClient::where("id_user_premieum",$request['id_user'])->first();
            if(!$clientUserPrem){
                $attach=UserPremieumHasClient::create([
                    "typeClient"=>$typeClient,
                    "id_client"=>$idClient,
                    "id_user_premieum"=>$userPrem->id_user_premieum
                ]);
            }else if(!($clientUserPrem->id_client==$idClient && $clientUserPrem->typeClient==$typeClient)){
                $clientUserPrem->delete();
                $attach=UserPremieumHasClient::create([
                    "typeClient"=>$typeClient,
                    "id_client"=>$idClient,
                    "id_user_premieum"=>$userPrem->id_user_premieum
                ]);
            }
        }
        $userPrem->save();
        $user->save();
        return response([
            "ok"=>true,
            "data"=>$this->getUserById($request["id_user"])
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        if(!empty($request['users']) && is_array($request['users'])){
            $deletedLis=[];
            foreach($request['users'] as $user){
                $userObj=UserPremieum::find($user);
                if($userObj){
                    $deletedLis [] = $user;
                    $user=User::find($userObj->id_user);
                    $user->delete();
                    $userObj->delete();
                }
            }
            return response([
                'ok'=>true,
                'data'=>"async",
                'users'=>$deletedLis
            ]);
        }
        return response([
            'ok'=>true,
            'data'=>"no action"
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show_sites(Request $request)
    {
        $idUser=$request['idUserPrem'];
        $typeShare=$request->get('type');
        $queryBuilder=ShareSite::query();
        $queryBuilder=$queryBuilder->where("id_user_premieum",$idUser)
        ->where('type_data_share',$typeShare);
        if($typeShare==="Site"){
            $queryBuilder=$queryBuilder->whereHas('site')
            ->with('site');
        }
        if($typeShare==="Departement"){
            $queryBuilder=$queryBuilder->whereHas('departement')
            ->with('departement');
        }
        if($typeShare==="Region"){
            $queryBuilder=$queryBuilder->whereHas('region')
            ->with('region');
        }
        $shareds=$queryBuilder->orderBy("id_share_site","DESC")
        ->paginate(12);
        foreach($shareds as &$share){
            $share->start=Carbon::parse($share->start)->format('d/m/y');
            $share->end=Carbon::parse($share->end)->format('d/m/y');
        }
        return response([
            "ok"=>true,
            "data"=>$shareds
        ],200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show_sessions(Request $request)
    {
        $idUser=$request['idUserPrem'];
        $users=UserSimple::where("created_by",$idUser)
        ->get();
        return response([
            "ok"=>true,
            "data"=>$users
        ],200);
    }
}