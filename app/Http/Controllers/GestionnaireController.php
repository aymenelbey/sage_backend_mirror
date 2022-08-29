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
use App\Models\GestionnaireHasSite;
use Validator;
use JWTAuth;
use App\Jobs\Export\ExportGestionnaires;

class GestionnaireController extends Controller
{
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
        $phone=$request->get('phone');$phone=$phone?$phone:$search;
        $email=$request->get('email');$email=$email?$email:$search;
        $status=$request->get('status');
        $sort=$request->get('sort');
        $sorter=$request->get('sorter');
        $function='where';
        $pageSize=$request->get('pageSize')?$request->get('pageSize'):20;
        $queryGestionaire = Gestionnaire::query();
        $queryGestionaire=$queryGestionaire->join("users","gestionnaires.id_user","=","users.id");
        if(in_array($status,['active','inactive'])){
            $queryGestionaire=$queryGestionaire->where("gestionnaires.status","=",$status=='active'?true:false);
        }
        $queryGestionaire = $queryGestionaire->where(function($query) use($nom,$prenom,$phone,&$function,$typeJoin,$email)  {
            if($nom){
                $query->{$function}("gestionnaires.nom","ILIKE","%{$nom}%");
                $function=$typeJoin=="inter"?"where":"orWhere";
            }
            if($prenom){
                $query->{$function}("gestionnaires.prenom","ILIKE","%{$prenom}%");
                $function=$typeJoin=="inter"?"where":"orWhere";
            }
            if($phone){
                $query->{$function}("gestionnaires.mobile","ILIKE","%{$phone}%");
                $function=$typeJoin=="inter"?"where":"orWhere";
            }
            if($email){
                $query->{$function}("gestionnaires.email","ILIKE","%{$email}%");
                $function=$typeJoin=="inter"?"where":"orWhere";
            }
        });
        if(in_array($sort,['ASC','DESC']) && in_array($sorter,['status','nom','prenom','telephone','email','mobile'])){
            $queryGestionaire=$queryGestionaire->orderBy("gestionnaires.".$sorter,$sort);
        }else{
            $queryGestionaire=$queryGestionaire->orderBy("gestionnaires.updated_at","DESC");
        }
        $gestionaires=$queryGestionaire->paginate($pageSize,["gestionnaires.status","gestionnaires.nom","gestionnaires.prenom","gestionnaires.mobile","gestionnaires.telephone","gestionnaires.email",'gestionnaires.nom','gestionnaires.prenom','users.init_password','users.username',"gestionnaires.id_gestionnaire"]);
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
        $this->validate($request,[
            "nom"=>["required"],
            "prenom"=>["required"],
            "genre"=>["required","in:MME,MR"],
            "societe"=>["required","in:Sage_engineering,Sage_expert,Sage_industry"],
            "mobile"=>["required"],
            "status"=>["required","boolean"],
            'telephone'=>['nullable','phone:FR'],
            "email"=>["email","unique:gestionnaires"]
        ]);
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
            "telephone"=>isset($request["telephone"])?$request["telephone"]:null,
            "mobile"=>isset($request["mobile"])?$request["mobile"]:null,
            "email"=>isset($request["email"])?$request["email"]:null,
            "societe"=>$request["societe"],
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
            ->find($idgestionaire,["users.username","users.init_password","gestionnaires.status","gestionnaires.genre","gestionnaires.nom","gestionnaires.prenom","gestionnaires.telephone","gestionnaires.mobile","gestionnaires.email","gestionnaires.societe","gestionnaires.id_gestionnaire"]);
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
            "societe"=>["required","in:Sage_engineering,Sage_expert,Sage_industry"],
            "mobile"=>["required"],
            'telephone'=>['nullable','phone:FR'],
            "status"=>["required","boolean"],
            "email"=>["email"]
        ]);
        $gestionaire = Gestionnaire::find($request["id_gestionnaire"]);
        $user=User::find($gestionaire->id_user);
        $gestionaire->update([
            "status"=>$request["status"],
            "genre"=>$request["genre"],
            "nom"=>$request["nom"],
            "prenom"=>$request["prenom"],
            "telephone"=>isset($request["telephone"])?$request["telephone"]:null,
            "mobile"=>$request["mobile"],
            "email"=>isset($request["email"])?$request["email"]:null,
            "societe"=>$request["societe"]
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
            "data"=>$request->only(["id_gestionnaire","status","genre","nom","username","init_password","prenom","telephone","mobile","email","societe"])
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
            $notDeletedLis=[];
            foreach($request['gestionnaires'] as $gestionnaire_id){

                try{
                    $gestionnaire = Gestionnaire::find($gestionnaire_id);
                    if($gestionnaire){
                        $canDelete = $gestionnaire->canDelete();
                        if($canDelete['can']){
                            Gestionnaire::destroy($gestionnaire_id);
                            $deletedLis[] = $gestionnaire_id;
                            
                        }else{
                            $notDeletedLis[$gestionnaire_id] = $canDelete['errors'];
                        }
    
                    }
                }catch(\Exception $e){
                    $notDeletedLis[$gestionnaire_id] = ['db.destroy-error'];
                }

                if(sizeof($request['gestionnaires']) == 1 && sizeof($notDeletedLis) == 1){
                    return response([
                        "errors" => true,
                        "message" => "item already in use",
                        "reasons" => $notDeletedLis
                    ], 402);
                }
            }

            return response([
                'ok'=>true,
                'data'=>"async",
                'gestionnaires'=>$deletedLis,
                'not_deleted' => $notDeletedLis,
            ], 200);
        }
        return response([
            'ok'=>true,
            'data'=>"no action"
        ],200);
    }
    /**
     * Remove the specified site attached to an manager from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy_sites(Request $request){
        if(isset($request['sites']) && is_array($request['sites'])){
            $deletedLis=[];
            foreach($request['sites'] as $site){
                 $geshassite =  GestionnaireHasSite::where([
                     ["id_gestionnaire","=",$request['gestionnaire']],
                     ["id_site","=",$site]
                 ])->first();
                if($geshassite){
                    $deletedLis [] = $site;
                    $geshassite->delete();
                }
            }
            return response([
                'ok'=>true,
                'data'=>"async",
                'sites'=>$deletedLis
            ],200);
        }
        return response([
            'ok'=>true,
            'data'=>"no action"
        ],200);
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
            ->whereNull("gestionnaire_has_sites.deleted_at")
            ->where("gestionnaire_has_sites.id_gestionnaire",$id_gestionnaire)
            ->paginate(20,["sites.id_site","sites.denomination","sites.categorieSite","sites.adresse","sites.latitude","sites.langititude","sites.siteIntrnet","sites.telephoneStandrad","sites.anneeCreation","sites.photoSite","sites.modeGestion","sites.perdiocitRelance"]);
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
        $search=$request->get('search');
        $typeJoin=$request->get('typeFilter');
        $categorieSite=$request->get('categorieSite');
        $modeGestion=$request->get('modeGestion');
        $address=$request->get('adresse');$address=$address?$address:$search;
        $denomination=$request->get('denomination');$denomination=$denomination?$denomination:$search;
        $telephoneStandrad=$request->get('telephoneStandrad');$telephoneStandrad=$telephoneStandrad?$telephoneStandrad:$search;
        $sort=$request->get('sort');
        $sorter=$request->get('sorter');
        $function='where';
        $pageSize=$request->get('pageSize')?$request->get('pageSize'):20;
        $siteQuery = Site::query();
        $siteQuery=$siteQuery->whereHas('gestionnaire', function($query) use ($gestionnaire) {
            $query->where('gestionnaire_has_sites.id_gestionnaire', $gestionnaire->id_gestionnaire);
        });
         if(!empty($denomination)){
            $siteQuery=$siteQuery->{$function}("denomination","ILIKE","%{$denomination}%");
            $function=$typeJoin=="inter"?"where":"orWhere";
        }
        if(in_array($categorieSite,["UVE","TRI","TMB","ISDND"])){
            $siteQuery=$siteQuery->{$function}("categorieSite","=","{$categorieSite}");
            $function=$typeJoin=="inter"?"where":"orWhere";
        }
        if(!empty($modeGestion)){
            $siteQuery=$siteQuery->{$function}("modeGestion","=","{$modeGestion}");
            $function=$typeJoin=="inter"?"where":"orWhere";
        }
        if($address){
            $siteQuery=$siteQuery->{$function}("adresse","ILIKE","%{$address}%");
            $function=$typeJoin=="inter"?"where":"orWhere";
        }
        if($telephoneStandrad){
            $siteQuery=$siteQuery->{$function}("telephoneStandrad","ILIKE","%{$telephoneStandrad}%");
            $function=$typeJoin=="inter"?"where":"orWhere";
        }
        if(in_array($sort,['ASC','DESC']) && in_array($sorter,["denomination","categorieSite","sinoe","adresse","sinoe","siteIntrnet","telephoneStandrad","anneeCreation","modeGestion","perdiocitRelance"])){
           $siteQuery=$siteQuery->orderBy($sorter,$sort);
        }else{
           $siteQuery=$siteQuery->orderBy("updated_at","DESC");
        }
        $sites=$siteQuery->paginate($pageSize);
        return response([
            "ok"=>true,
            "data"=> $sites
        ],200);
    }

    public function export(Request $request) {
        ExportGestionnaires::dispatch($request->user(), "gestionnaires", "/manager");

        return response([
            "ok" => true,
            "data" => "no action",
        ], 200);
    }
    
}