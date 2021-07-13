<?php
namespace App\Http\Controllers\Users;

use JWTAuth;
use App\Models\User;
use App\Models\UserPremieum;
use App\Models\ShareSite;
use App\Models\Site;
use App\Models\DataTechn;
use App\Models\DataTechnTMB;
use App\Models\DataTechnISDND;
use App\Models\DataTechnTRI;
use App\Models\DataTechnUVE;
use App\Models\Admin;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator;
use DB;

class UserSitesController extends Controller{

    const BASE_SITE=["denomination","categorieSite","adresse","latitude","langititude","siteIntrnet","telephoneStandrad","anneeCreation","photoSite","modeGestion","perdiocitRelance"];
    const DATA_TECH_TMB=["quantiteRefus","CSRProduit","envoiPreparation","tonnageAnnuel","capaciteNominal","dernierConstruct","typeInstallation","typeDechetAccepter","technologie","valorisationEnergitique","autreActivite"];
    const DATA_TECH_TRI=["capaciteHoraire","capaciteNominale","capaciteReglementaire","dateExtension","miseEnService","dernierConstructeur","extension"];
    const DATA_TECH_UVE=['nombreFours',"capacite","nombreChaudiere","debitEau","miseEnService","typeFoursChaudiere","capaciteMaxAnu","videFour","reseauChaleur","rsCommentaire","tonnageReglementaireAp","performenceEnergetique","cycleVapeur","terboalternateur","venteProduction","typeDechetRecus","traitementFumee","installationComplementair","voiTraiFemuee","traitementNOX","equipeProcessTF","reactif","typeTerboalternateur","constructeurInstallation"];
    const DATA_TECH_ISDND=["capaciteNominale","capaciteRestante","capaciteReglementaire","projetExtension","dateExtension","dateOuverture","dateFermeture","dateFermeturePrev"];

    public function show_sites(Request $request){
        $user = JWTAuth::user();
        $userPrem=UserPremieum::where("id_user",$user->id)
        ->first();
        $dataCompare=Carbon::now()->format('Y-m-d');
        $sites=ShareSite::join("sites",function($join){
            $join->on(function($query){
                $query->on("sites.id_site","=","share_sites.id_data_share")
                ->where('share_sites.type_data_share',"=",'Site');
            })
            ->orOn(function($query){
                $query->on("sites.departement_siege","=","share_sites.id_data_share")
                ->whereExists(function ($query) {
                    $query->select("type_shared_sites.id_type_shared_site")
                        ->from('type_shared_sites')
                        ->whereRaw('type_shared_sites.id_share_site = share_sites.id_share_site')
                        ->whereRaw('type_shared_sites.site_categorie = "sites"."categorieSite"');
                })
                ->where('share_sites.type_data_share',"=",'Departement');
            })
            ->orOn(function($query){
                $query->on("sites.region_siege","=","share_sites.id_data_share")
                ->whereExists(function ($query) {
                    $query->select("type_shared_sites.id_type_shared_site")
                        ->from('type_shared_sites')
                        ->whereRaw('type_shared_sites.id_share_site = share_sites.id_share_site')
                        ->whereRaw('type_shared_sites.site_categorie = "sites"."categorieSite"');
                })
                ->where('share_sites.type_data_share',"=",'Region');
                
            });
        })
        ->where("share_sites.id_user_premieum",$userPrem->id_user_premieum)
        ->where("share_sites.is_blocked",false)
        ->where("share_sites.start","<=",$dataCompare)
        ->where("share_sites.end",">=",$dataCompare);
        $sql=$sites->toSql();
        $sites=$sites->distinct('sites.id_site')
        ->get(["sites.id_site","sites.adresse","sites.langititude AS lang","sites.latitude AS lat","share_sites.id_share_site AS id_access","sites.categorieSite AS iconType"]);
        return response([
            'ok'=>true,
            'data'=>$sites,
            "sql"=>$sql
        ],200);
    }
    public function show_detail(Request $request){
        $user = JWTAuth::user();
        $userPrem=UserPremieum::where("id_user",$user->id)
        ->first();
        $idShare=$request["idShare"];
        $idSite=$request["idSite"];
        $detail=ShareSite::where("id_user_premieum",$userPrem->id_user_premieum)
        ->where("end",">=",Carbon::now()->format('Y-m-d'))
        ->where("start","<=",Carbon::now()->format('Y-m-d'))
        ->where("id_share_site",$idShare)
        ->first();
        if($detail){
            $clmnSite=[]; 
            $clmnTech=[];
            if($detail->type_data_share=="Site"){
                $clmnSite=array_intersect(array_keys($detail->columns),self::BASE_SITE);
                if(!in_array("categorieSite",$clmnSite)){
                    $clmnSite[]="categorieSite";    
                }
                $clmnSite[]="id_site";
                $site=Site::where("id_site",$detail->id_data_share)
                ->first($clmnSite);
                
            }else{
                $clmnSite=array_intersect(array_keys($detail->columns['generalInfo']),self::BASE_SITE);
                if(!in_array("categorieSite",$clmnSite)){
                    $clmnSite[]="categorieSite";    
                }
                $clmnSite[]="id_site";
                $site=Site::where("id_site",$idSite);
                if($detail->type_data_share=="Departement"){
                    $site=$site->where("departement_siege",$detail->id_data_share);
                }else{
                    $site=$site->where("region_siege",$detail->id_data_share);
                }
                $site=$site->first($clmnSite);
            }
            if($site){
                if($detail->type_data_share=="Site"){
                    $clmnTech=array_intersect(array_keys($detail->columns),constant("self::DATA_TECH_".$site->categorieSite));
                }else{
                    $clmnTech=array_intersect(array_keys($detail->columns[$site->categorieSite]),constant("self::DATA_TECH_".$site->categorieSite));
                }
                $dataTech=DataTechn::where("id_site",$site->id_site)
                ->first();
                $techClassName='App\Models\DataTechn'.$site->categorieSite;
                $techData=$techClassName::find($dataTech->id_data_tech,$clmnTech);
                $photos=array_column($site->photos->toArray(),"url");
                $site=$site->toArray();
                unset($site["photos"]);unset($site["id_site"]);
                return response([
                    'ok'=>true,
                    'data'=>[
                        "infoBase"=>$site,
                        "infoTech"=>$techData,
                        "photos"=>$photos
                    ]
                ],200);
            }
        }
        return response([
            'ok'=>"server",
            'data'=>"permission denied"
        ],401);
    }
}