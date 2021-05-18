<?php

namespace App\Http\Middleware;

use JWTAuth;
use Closure;
use Illuminate\Http\Request;
use App\Models\Gestionnaire;
use App\Models\Site;
use App\Models\GestionnaireHasSite;

class GestionnaireHasPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next,$role)
    {
        $gestionnaire = JWTAuth::user();
        $gestionnaire = Gestionnaire::where("id_user","=",$gestionnaire->id)->first();
        $checkIds=[];
        switch($role){
            case "idSite":
                if(!empty($request['idSite'])){
                    $checkIds []=$request['idSite'];
                }
                break;
            case "delete":
                $checkIds=isset($request['sites'])&&is_array($request['sites'])?$request['sites']:[];
                break;
            case "update":
                if(!empty($request['siteInfo']["id_site"])){
                    $checkIds []=$request['siteInfo']["id_site"];
                }
                break;
        }
        $isPassed=true;
        foreach($checkIds as $id){
            if(!(Site::where('id_site',$id)->exists() && GestionnaireHasSite::where('id_site',$id)->where('id_gestionnaire',$gestionnaire->id_gestionnaire)->exists())){
                $isPassed=false;
                break;
            }
        }
        if($isPassed){
            return $next($request);
        }else{
            return response([
                "message"=>'Premission denied',
                "errors"=>"You have not permission to controle this site"
            ],401);
        }
    }
}