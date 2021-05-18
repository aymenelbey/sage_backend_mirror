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
        $sites=ShareSite::join("sites","sites.id_site","=","share_sites.id_site")
        ->where("share_sites.id_user_premieum",$userPrem->id_user_premieum)
        ->where("share_sites.is_blocked",false)
        ->where("share_sites.end",">=",Carbon::now()->format('Y-m-d'))
        ->where("share_sites.start","<=",Carbon::now()->format('Y-m-d'))
        ->get(["sites.adresse","sites.langititude AS lang","sites.latitude AS lat","share_sites.id_share_site AS id_access","sites.categorieSite AS iconType"]);
        return response([
            'ok'=>true,
            'data'=>$sites
        ],200);
    }

    public function show_detail(Request $request){
        $user = JWTAuth::user();
        $userPrem=UserPremieum::where("id_user",$user->id)
        ->first();
        $detail=ShareSite::where("id_user_premieum",$userPrem->id_user_premieum)
        ->where("end",">=",Carbon::now()->format('Y-m-d'))
        ->where("start","<=",Carbon::now()->format('Y-m-d'))
        ->where("id_share_site",$request["idShare"])
        ->first();
        if($detail){
            $columns=array_filter(explode("|",$detail->columns));
            $clmnSite=array_intersect($columns,self::BASE_SITE);
            $clmnSite[]="categorieSite";
            $clmnSite[]="id_site";
            $site=Site::where("id_site",$detail->id_site)
            ->first($clmnSite);
            $dataTech=DataTechn::where("id_site",$detail->id_site)
            ->first();
            $clmnSite=array_intersect($columns,constant("self::DATA_TECH_".$site->categorieSite));
            $techClassName='App\Models\DataTechn'.$site->categorieSite;
            $techData=$techClassName::find($dataTech->id_data_tech,$clmnSite);
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
        return response([
            'ok'=>"server",
            'data'=>"permission denied"
        ],401);
    }
}