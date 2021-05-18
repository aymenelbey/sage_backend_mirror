<?php

namespace App\Http\Controllers;

use App\Models\Gestionnaire;
use App\Models\Site;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Validator;
use JWTAuth;

class GestionnaireController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request){
        $nom=$request->get('nom');
        $prenom=$request->get('prenom');
        $phone1=$request->get('phone1');
        $function='where';
        $pageSize=$request->get('pageSize')?$request->get('pageSize'):10;
        $queryGestionaire = Gestionnaire::query();
        $queryGestionaire=$queryGestionaire->join("users","gestionnaires.id_user","=","users.id");
        if($nom){
            $queryGestionaire=$queryGestionaire->{$function}("gestionnaires.nom","ILIKE","%{$nom}%");
            $function='orWhere';
        }
        if($prenom){
            $queryGestionaire=$queryGestionaire->{$function}("gestionnaires.prenom","ILIKE","%{$prenom}%");
            $function='orWhere';
        }
        if($phone1){
            $queryGestionaire=$queryGestionaire->{$function}("gestionnaires.telephone1","ILIKE","%{$phone1}%");
            $function='orWhere';
        }
        $gestionaires=$queryGestionaire->orderBy("gestionnaires.created_at","DESC")
        ->paginate($pageSize);
        return response([
            "ok"=> true,
            "data"=>$gestionaires
        ],200);
    }
    public function listSites(Request $request){
        $gest = Gestionnaire::where("id_gestionnaire","=",$request["id"])->with("sites")->first();
        if($gest){
            return response([
                "ok"=>true,
                "data"=>$gest
            ],200);
        }
        return response([
            "ok"=>false,
            "errors"=>"Gestionnaire n'existe pas"
        ],400);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request){
        $rules = [
            "nom"=>["required"],
            "prenom"=>["required"],
            "genre"=>["required","in:MME,MR"],
            "status"=>["required","boolean"],
            "email"=>["email","unique:gestionnaires"]
        ];
        $messages = [
            "required"=> ":attribute est obligatoire",
            "email"=>":attribute doit être un email valide",
            "unique"=>"Veuillez choisir un :attribute unique"
        ];
        $validator = Validator::make($request->all(),$rules,$messages);
        if ($validator->fails()) {
            return response([
                "ok"=> "server",
                "errors"=>$validator->errors()
            ],400);        
        }
        $adminuser = JWTAuth::user();
        $admin = Admin::where("id_user","=",$adminuser->id)->select("id_admin")->first();
        $username=User::getUsername($request['nom'],$request['prenom']);
        $password=Str::random(10);
        $user =  User::create([
            "username"=>$username,
            "typeuser"=>"Gestionnaire",
            "password"=>Hash::make($password),
            "init_password"=>$password
        ]);
        $gestionnaire = Gestionnaire::create([
            "status"=>$request["status"],
            "genre"=>$request["genre"],
            "nom"=>$request["nom"],
            "prenom"=>$request["prenom"],
            "telephone1"=>isset($request["telephone1"])?$request["telephone1"]:null,
            "telephone2"=>isset($request["telephone2"])?$request["telephone2"]:null,
            "mobile1"=>isset($request["mobile1"])?$request["mobile1"]:null,
            "mobile2"=>isset($request["mobile2"])?$request["mobile2"]:null,
            "email"=>isset($request["email"])?$request["email"]:null,
            "contract"=>isset($request["contract"])?$request["contract"]:null,
            'id_user'=>$user->id,
            "id_admin"=> $admin->id_admin
        ]);
        return response([
            "ok"=> true,
            "data"=>$user
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        if(!empty($request['idgestionnaire'])){
            $idgestionaire=$request['idgestionnaire'];
            $gestionaire=Gestionnaire::join("users","users.id","=","gestionnaires.id_user")
            ->find($idgestionaire,["users.username","users.init_password","gestionnaires.status","gestionnaires.genre","gestionnaires.nom","gestionnaires.prenom","gestionnaires.telephone1","gestionnaires.telephone2","gestionnaires.mobile1","gestionnaires.mobile2","gestionnaires.email","gestionnaires.contract","gestionnaires.id_gestionnaire"]);
            if($gestionaire){
                return response([
                    'ok'=>true,
                    'data'=>$gestionaire
                ],200);
            }
        }
        return response([
            'ok'=>'server',
            'errors'=>'Aucune gestionaire disponible'
        ],400);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        if(!empty($request['idgestionnaire'])){
            $idgestionaire=$request['idgestionnaire'];
            $gestionaire=Gestionnaire::find($idgestionaire);
            if($gestionaire){
                return response([
                    'ok'=>true,
                    'data'=>$gestionaire
                ],200);
            }
        }
        return response([
            'ok'=>'server',
            'errors'=>'Aucune gestionaire disponible'
        ],400);
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
            "id_gestionnaire"=>["required","exists:gestionnaires"],
            "nom"=>["required"],
            "prenom"=>["required"],
            "genre"=>["required","in:MME,MR"],
            "username"=>["required"],
            "status"=>["required","boolean"]
        ],[
            "required"=> ":attribute est obligatoire",
            "email"=>":attribute doit être un email valide",
            "unique"=>"Veuillez choisir un :attribute unique"
        ]);
        $gestionaire = Gestionnaire::find($request["id_gestionnaire"]);
        $user=User::find($gestionaire->id_user);
        $gestionaire->update([
            "status"=>$request["status"],
            "genre"=>$request["genre"],
            "nom"=>$request["nom"],
            "prenom"=>$request["prenom"],
            "telephone1"=>isset($request["telephone1"])?$request["telephone1"]:null,
            "telephone2"=>isset($request["telephone2"])?$request["telephone2"]:null,
            "mobile1"=>isset($request["mobile1"])?$request["mobile1"]:null,
            "mobile2"=>isset($request["mobile2"])?$request["mobile2"]:null,
            "email"=>isset($request["email"])?$request["email"]:null,
            "contract"=>isset($request["contract"])?$request["contract"]:null,
        ]);
        if($user->init_password && ($user->init_password!=$request['init_password'])){
            $user->password=Hash::make($request['init_password']);
            $user->init_password=$request['init_password'];
        }
        if($user->username!=$request['username'] && !User::where('username', $request['username'] )->exists()){
             $user->username=$request['username'];
        }
        $user->save();
        return response([
            "ok"=>"server",
            "data"=>"Gestionnaire modifier"
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
        if(isset($request['gestionnaires']) && is_array($request['gestionnaires'])){
            $deletedLis=[];
            foreach($request['gestionnaires'] as $gestionnaire){
                $gestion=Gestionnaire::find($gestionnaire);
                if($gestion){
                    $deletedLis [] = $gestionnaire;
                    $gestion->delete();
                }
            }
            return response([
                'ok'=>true,
                'data'=>"async",
                'gestionnaires'=>$deletedLis
            ]);
        }
        return response([
            'ok'=>true,
            'data'=>"no action"
        ]);
    }
    /**
     * Show the list of attached sites of the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show_sites(Request $request)
    {
        if(!empty($request['idgestionnaire'])){
            $id_gestionnaire=$request['idgestionnaire'];
            $sites=Site::join("gestionnaire_has_sites","gestionnaire_has_sites.id_site","=","sites.id_site")
            ->where("gestionnaire_has_sites.id_gestionnaire",$id_gestionnaire)
            ->get(["sites.id_site","sites.denomination","sites.categorieSite","sites.adresse","sites.latitude","sites.langititude","sites.siteIntrnet","sites.telephoneStandrad","sites.anneeCreation","sites.photoSite","sites.modeGestion","sites.perdiocitRelance"]);
            return response([
                'ok'=>true,
                'data'=>$sites
            ],200);
            
        }
        return response([
            'ok'=>"server",
            'data'=>"no action"
        ],400);
    }

    /**
     * Show the list of sites shared with the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function list_sites(Request $request)
    {
        $gestionnaire = JWTAuth::user();
        $gestionnaire = Gestionnaire::where("id_user","=",$gestionnaire->id)->first();
        $cateSite=$request->get('cateSite');
        $modGest=$request->get('modGest');
        $address=$request->get('address');
        $function='where';
        $pageSize=$request->get('pageSize')?$request->get('pageSize'):10;
        $siteQuery = Site::query();
        $siteQuery=$siteQuery->whereHas('gestionnaire', function($query) use ($gestionnaire) {
            $query->where('gestionnaire_has_sites.id_gestionnaire', $gestionnaire->id_gestionnaire);
        });
        //innerJoin('gestionnaire_has_sites','gestionnaire_has_sites.id_site','=','sites.id_site');
        if($cateSite){
            $siteQuery=$siteQuery->{$function}("categorieSite","ILIKE","{$cateSite}");
            $function='orWhere';
        }
        if($modGest){
            $siteQuery=$siteQuery->{$function}("modeGestion","ILIKE","{$modGest}");
            $function='orWhere';
        }
        if($address){
            $siteQuery=$siteQuery->{$function}("adresse","ILIKE","%{$address}%");
            $function='orWhere';
        }
        $sites=$siteQuery->orderBy("created_at","DESC")
        ->paginate($pageSize);
        return response([
            "ok"=>true,
            "data"=> $sites
        ],200);
    }
    
}