<?php
namespace App\Http\Controllers\Users;

use JWTAuth;
use App\Models\User;
use App\Models\UserPremieum;
use App\Models\UserSimple;
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
use Illuminate\Support\Facades\Storage;


class UserSitesController extends Controller{

    const BASE_SITE=["denomination","categorieSite","adresse","latitude","langititude","siteIntrnet","telephoneStandrad","anneeCreation","photoSite","modeGestion","perdiocitRelance"];
    const DATA_TECH_TMB=["quantiteRefus","CSRProduit","envoiPreparation","tonnageAnnuel","capaciteNominal","dernierConstruct","typeInstallation","typeDechetAccepter","technologie","valorisationEnergitique","autreActivite"];
    const DATA_TECH_TRI=["capaciteHoraire","capaciteNominale","capaciteReglementaire","dateExtension","miseEnService","dernierConstructeur","extension"];
    const DATA_TECH_UVE=['nombreFours',"capacite","nombreChaudiere","debitEau","miseEnService","typeFoursChaudiere","capaciteMaxAnu","videFour","reseauChaleur","rsCommentaire","tonnageReglementaireAp","performenceEnergetique","cycleVapeur","terboalternateur","venteProduction","typeDechetRecus","traitementFumee","installationComplementair","voiTraiFemuee","traitementNOX","equipeProcessTF","reactif","typeTerboalternateur","constructeurInstallation"];
    const DATA_TECH_ISDND=["capaciteNominale","capaciteRestante","capaciteReglementaire","projetExtension","dateExtension","dateOuverture","dateFermeture","dateFermeturePrev"];
    const DATA_CLIENT=['client_nomEpic'=>'nomEpic','client_nom_court'=>'nom_court','client_serin'=>'serin','client_siteInternet'=>'siteInternet','client_telephoneStandard'=>'telephoneStandard','client_nature_juridique'=>'nature_juridique','client_nomCourt'=>'nomCourt','client_denominationLegale'=>'denominationLegale','client_sinoe'=>'sinoe','client_amobe'=>'amobe','client_nomCommune'=>'nomCommune','client_insee'=>'insee','client_adresse'=>'adresse','client_city'=>'city','client_country'=>'country','client_postcode'=>'postcode','client_region_siege'=>'region_siege','client_departement_siege'=>'departement_siege','client_nombreHabitant'=>'nombreHabitant'];
    const DATA_COMPANY=['company_groupe'=>'groupe','company_denomination'=>'denomination','company_serin'=>'serin','company_sinoe'=>'sinoe','company_nature_juridique'=>'nature_juridique','company_codeape'=>'codeape','company_siteInternet'=>'siteInternet','company_telephoneStandard'=>'telephoneStandard','company_effectifs'=>'effectifs','company_adresse'=>'adresse','company_city'=>'city','company_country'=>'country','company_postcode'=>'postcode'];

    public function show_sites(Request $request){
        $user = JWTAuth::user();
        $lat=$request['lat'];$lang=$request['lang'];
        $search=$request->search;
        $categorie=$request->categorie;
        $modeGest=$request->modeGest;
        if($user->typeuser=="UserPremieume"){
            $userPrem=UserPremieum::where("id_user",$user->id)
            ->first();
            $idUserPrem=$userPrem->id_user_premieum;
        }else{
            $userSimp=UserSimple::where("id_user",$user->id)
            ->first();
            $userPrem=UserPremieum::where("id_user",$userSimp->created_by)
            ->first();
            $idUserPrem=$userPrem->id_user_premieum;
        }
        $compareFunc='where';
        $idUserPrem=$userPrem->id_user_premieum;
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
        ->where("share_sites.id_user_premieum",$idUserPrem)
        ->where("share_sites.is_blocked",false)
        ->where("share_sites.start","<=",$dataCompare)
        ->where("share_sites.end",">=",$dataCompare)
        ->whereNull("share_sites.deleted_at");
        if($search){
            $fields=$request['fields'];
            if(is_array($fields)){
                foreach($fields as $field){
                    $field=json_decode($field)->index;
                    if(in_array($field,self::BASE_SITE)){
                        $sites=$sites->{$compareFunc}("sites.".$field,'ilike',"%$search%");
                        $compareFunc="orWhere";
                    }
                }
            }
        }
        if(in_array($categorie,["UVE","TRI","TMB","ISDND"])){
            $sites=$sites->where("sites.categorieSite",$categorie);
        }
        if(!empty($modeGest)){
            $sites=$sites->{$compareFunc}("sites.modeGestion",$modeGest);
            $compareFunc="orWhere";
        }
        $regions=$request['regions'];
        if(is_array($regions)){
            $arrayList=[];
            foreach($regions as $region){
                $arrayList []=(int)json_decode($region)->index;
            }
            $sites=$sites->{$compareFunc."In"}("sites.region_siege",$arrayList);
            $compareFunc="orWhere";
        }
        $departments=$request['departments'];
        if(is_array($departments)){
            $arrayList=[];
            foreach($departments as $department){
                $arrayList []=(int)json_decode($department)->index;
            }
            $sites=$sites->{$compareFunc."In"}("sites.departement_siege",$arrayList);
            $compareFunc="orWhere";
        }
        /*if($lat){
            $lat=explode(',',$lat);
            $sites=$sites->whereBetween("sites.latitude",$lat);
        }
        if($lang){
            $lang=explode(',',$lang);
            $sites=$sites->whereBetween("sites.langititude",$lang);
        }*/
        $sites=$sites->distinct('sites.id_site')
        ->get(["sites.id_site","sites.adresse","sites.denomination","sites.langititude AS lang","sites.latitude AS lat","share_sites.id_share_site AS id_access","sites.categorieSite AS iconType"]);
        return response([
            'ok'=>true,
            'data'=>$sites
        ],200);
    }
    public function show_detail(Request $request){
        $user = JWTAuth::user();
        if($user->typeuser=="UserPremieume"){
            $userPrem=UserPremieum::where("id_user",$user->id)
            ->first();
            $idUserPrem=$userPrem->id_user_premieum;
        }else{
            $userSimp=UserSimple::where("id_user",$user->id)
            ->first();
            $userPrem=UserPremieum::where("id_user",$userSimp->created_by)
            ->first();
            $idUserPrem=$userPrem->id_user_premieum;
        }
        $idUserPrem=$userPrem->id_user_premieum;
        $idShare=$request["idShare"];
        $idSite=$request["idSite"];
        $detail=ShareSite::where("id_user_premieum",$idUserPrem)
        ->where("end",">=",Carbon::now()->format('Y-m-d'))
        ->where("start","<=",Carbon::now()->format('Y-m-d'))
        ->where("id_share_site",$idShare)
        ->whereNull("deleted_at")
        ->first();
        $client=[];
        $company=[];
        if($detail){
            $clmnSite=[]; 
            $clmnTech=[];
            if($detail->type_data_share=="Site"){
   
                $site=Site::where("id_site",$detail->id_data_share)
                ->with(['client.client','exploitant.client'])
                ->first();

                if($detail['files'] && !empty($detail['files'])){
                    
                    $file_categories = array_filter($detail['files'], function($key, $value) use ($detail){
                        return $detail['files'][$value];
                    }, ARRAY_FILTER_USE_BOTH);

                    $files = $site->files(array_keys($file_categories))->where('shareable', '1')->get();
                }else{
                    $files = [];
                }
                foreach($files as $file){
                    $file->name = asset(Storage::url("GED/".$file->name));
                }
            }else{

                $site= Site::where("id_site",$idSite)->with(['client.client','exploitant.client']);
                
                if($detail->type_data_share=="Departement"){
                    $site=$site->where("departement_siege",$detail->id_data_share);
                }else{
                    $site=$site->where("region_siege",$detail->id_data_share);
                }
                $site=$site->first();

                
                    
                $file_categories = array_filter($detail['files'], function($key, $value) use ($detail){
                    return $detail['files'][$value];
                }, ARRAY_FILTER_USE_BOTH);
                

                $files = $site->files(array_keys($file_categories))->where('shareable', '1')->get();

                foreach($files as $file){
                    $file->name = asset(Storage::url("GED/".$file->name));
                }
            }
            if($site){

                $dataTech = DataTechn::where("id_site",$site->id_site)->first();

                $techClassName='App\Models\DataTechn'.$site->categorieSite;
                
                $newTechData = [];

                $techData = $techClassName::find($dataTech->id_data_tech);
                
                // $techData  = $techData->toArray();
                

                if(isset($detail['columns'][$site->categorieSite])){
                    
                    if(in_array($site->categorieSite, ["TRI","TMB","ISDND"])){
                        
                        foreach($techData  as $key => $value){
                            if(isset($detail['columns'][$site->categorieSite][$key]) && $detail['columns'][$site->categorieSite][$key]){
                                $newTechData[$key] = $value;
                            }
                        }

                    }else if($site->categorieSite == 'UVE'){
                        $techData = $techData->withEnums();
                        $newTechData = ['infos' => [], 'lines' => [], 'valorisations' => ['blocks' => []]];
                        foreach($techData['infos']  as $key => $value){
                            if(isset($detail['columns'][$site->categorieSite]['infos'][$key]) && $detail['columns'][$site->categorieSite]['infos'][$key]){
                                $newTechData['infos'][$key] = $value;
                            }
                        }
                        foreach($techData['lines'] as $index => $line){
                            $newTechData['lines'][$index] = [];
                            foreach($line as $key => $value){
                                if(isset($detail['columns'][$site->categorieSite]['lines'][$key]) && $detail['columns'][$site->categorieSite]['lines'][$key]){
                                    $newTechData['lines'][$index][$key] = $value;
                                }
                            }
                        }
                        foreach($techData['valorisations']  as $key => $value){
                            if(isset($detail['columns'][$site->categorieSite]['valorisations'][$key]) && $detail['columns'][$site->categorieSite]['valorisations'][$key]){
                                $newTechData['valorisations'][$key] = $value;
                            }
                        }

                        foreach($techData['valorisations']['blocks']  as $index => $block){
                            $newTechData['valorisations']['blocks'][$index] = [];
                            foreach($block as $key => $value){
                                if(isset($detail['columns'][$site->categorieSite]['valorisations'][$key]) && $detail['columns'][$site->categorieSite]['valorisations'][$key]){
                                    $newTechData['valorisations']['blocks'][$index][$key] = $value;
                                }
                            }
                        }

                    }else{
                        $newTechData = $techData->toArray();
                    }

                }else{
                    $newTechData = $techData->toArray();
                }

                $photos = array_column($site->photos->toArray(),"url");

                $client = [];
                $tmpClient=$site->client->client->toArray();
                foreach($tmpClient  as $key => $value){
                    if(isset($detail['columns']['Client']["client_$key"]) && $detail['columns']['Client']["client_$key"]){
                        $client[$key] = $value;
                    }
                }

                $company = [];
                $tmpCompany = $site->exploitant->client->toArray();

                foreach($tmpCompany as $key => $value){
                    if(isset($detail['columns']['Company']["company_$key"]) && $detail['columns']['Company']["company_$key"]){
                        $company[$key] = $value;
                    }
                }

                $finalSite = [];
                $site=$site->toArray();
                unset($site["photos"]);unset($site["id_site"]);unset($site["exploitant"]);unset($site["client"]);
                foreach($site as $key => $value){
                    if(isset($detail['columns']['generalInfo'][$key]) && $detail['columns']['generalInfo'][$key]){
                        $finalSite[$key] = $value;
                    }
                }
                return response([
                    'ok'=>true,
                    'data'=>[
                        'share' => $detail,
                        "infoBase" => $finalSite,
                        "infoTech" => $newTechData,
                        'originalInfoTech' => $dataTech,
                        'client'=> $client,
                        'company'=> $company,
                        "photos"=> $photos,
                        "files" => $files,
                        "categories" => $file_categories,
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