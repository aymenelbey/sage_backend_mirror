<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\DataTechn;
use App\Models\DataTechnTMB;
use App\Models\DataTechnISDND;
use App\Models\DataTechnTRI;
use App\Models\DataTechnUVE;
use App\Models\ImageSage;
use App\Models\GestionnaireHasSite;
use App\Models\Admin;
use App\Models\Collectivite;
use App\Models\SocieteExpSite;
use App\Models\ClientHasSite;
use App\Models\SocieteExploitant;
use App\Models\EPIC;
use App\Models\Syndicat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use App\Http\Helpers\SiteHelper;
use JWTAuth;
use App\Jobs\Export\ExportSites;
use App\Exports\ArrayExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Helpers\ExportHelper;

class SiteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request){
        $all=$request->get('all');
        $search=$request->get('search');
        $typeJoin=$request->get('typeFilter');
        $categorieSite=$request->get('categorieSite');
        
        $status = $request->get('status');
        
        $modeGestion=$request->get('modeGestion');
        $address=$request->get('adresse');$address=$address?$address:$search;
        $denomination=$request->get('denomination');$denomination=$denomination?$denomination:$search;
        $telephoneStandrad=$request->get('telephoneStandrad');$telephoneStandrad=$telephoneStandrad?$telephoneStandrad:$search;
        $sort=$request->get('sort');
        $sorter=$request->get('sorter');
        $function='where';
        $pageSize=$request->get('pageSize')?$request->get('pageSize'):20;
        $siteQuery = Site::query();
        if(!empty($denomination)){
            $siteQuery=$siteQuery->{$function}("denomination","ILIKE","%{$denomination}%");
            $function=$typeJoin=="inter"?"where":"orWhere";
        }
        if(in_array($categorieSite,["UVE","TRI","TMB","ISDND"])){
            $siteQuery=$siteQuery->{$function}("categorieSite","=","{$categorieSite}");
            $function=$typeJoin=="inter"?"where":"orWhere";
        }

        if(!empty($status)){
            $siteQuery = $siteQuery->whereIn("status", $status);
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
        if($all){
            $sites=$siteQuery->get();
        }else{
            $sites=$siteQuery->paginate($pageSize);
        }
        
        return response([
            "ok"=>true,
            "data"=> $sites
        ],200);

    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $this->validate($request,[
            "id_site"=>['exists:sites,id_site']
        ]);
        $site = Site::with(['client.client', 'exploitant.client','dataTech.dataTech',"gestionnaire","contracts.communes","contracts.contractant","contracts.site", "departement_siege",'region_siege', 'updated_by', 'status_updated_by'])
        ->find($request['id_site']);

        $site->dataTech->dataTech->withEnums();
        
        if(isset($site->exploitant) && isset($site->exploitant->client)){
            $site->exploitant->client->withEnums();
        }

        $siteReturn=$site->toArray();
        $siteReturn['photos']=$site->photos->map(function($photo){
            return $photo->__toString();
        });
        $siteReturn['files'] = $site->files(null)->get();
        foreach($siteReturn['files'] as $file){
            $file->entity = $file->entity();
            $file->path = $file->getPath();
        }
        $siteReturn['departement_siege']=!empty($siteReturn['departement_siege']['name_departement'])?$siteReturn['departement_siege']['name_departement']:'';
        $siteReturn['region_siege']=!empty($siteReturn['region_siege']['name_region'])?$siteReturn['region_siege']['name_region']:'';
        return response([
            "ok"=>true,
            "data"=>$siteReturn
        ],200);
    }
/**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function addSitesToClient(Request $request){
        $validator = Validator::make($request->all(),[
            "id_collectivite"=>"exists:collectivites",
            "id_sites"=>"required"
        ],[
            "id_sites.required"=>"La list des sites d'existe pas",
            "id_collectivite.exists"=>"Client n'existe pas"
        ]);
        if($validator->fails()){
            return response([
                "ok"=>true,
                "message"=>$validator->errors()
            ],400);
        }
        $array_sites = [];
        $message = ["id_site.exists"=>"Site n'existe pas"];
        foreach($request["id_sites"] as $id){
            $val = Validator::make(["id_site"=>$id],["id_site"=>"exists:sites"],$message);
            if($val->fails()){
                return response([
                    "ok"=>false,
                    "message"=>$val->errors()
                ],400);
            }
            $temp = [
                "id_site"=>$id,
                "id_collectivite"=>$request["id_collectivite"],
                "created_at"=>Carbon::now(),
                "updated_at"=>Carbon::now()
            ];
            array_push($array_sites,$temp);
        }
        $client_has_site = ClientHasSite::insert($array_sites);
        return response([
            "ok"=>true,
            "message"=>$client_has_site
        ],200);
        
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request){
        $siteInfo=$request->siteInfo;
        $validator=SiteHelper::validateSiteInfo($request['siteInfo']);
        if ($validator->fails()) {
            return response([
                "message"=> "The given data was invalid.",
                "level"=>0,
                "errors"=>$validator->errors()
            ],401);        
        }
        $techData=SiteHelper::extractTechData(collect($request[$siteInfo["categorieSite"]]),$siteInfo["categorieSite"]);
        $useradmin = JWTAuth::user();
        $useradmin = Admin::where("id_user","=",$useradmin->id)->select("id_admin")->first();
        $site = Site::create(SiteHelper::extractSiteData(collect($siteInfo)));
        $geshassite =  GestionnaireHasSite::create([
            'id_admin'=>$useradmin->id_admin,
            'id_gestionnaire'=>$siteInfo["gestionaire"],
            'id_site'=>$site->id_site
        ]);
        $clienthassite = ClientHasSite::create([
            "id_site"=>$site->id_site,
            "id_collectivite"=>$siteInfo["client"]
        ]);
        $societe = SocieteExpSite::create([
            "typeExploitant"=>$siteInfo["typeExploitant"],
            "id_client"=>$siteInfo["societe"],
            "id_site"=>$site->id_site
        ]);
        $techClassName='App\Models\DataTechn'.$siteInfo["categorieSite"];
        $dataTech=$techClassName::create($techData);
        $dataTech = DataTechn::create([
            "id_site"=>$site->id_site,
            "typesite"=>$siteInfo["categorieSite"],
            "id_data_tech"=>$dataTech->{"id_data_".strtolower($siteInfo["categorieSite"])}
        ]);
        if(is_array($request['siteInfo']['photos'])){
            foreach($request['siteInfo']['photos'] as $image){
                ImageSage::where("uid",$image['uid'])
                ->update([
                    "ref_id"=>$site->id_site
                ]);
            }
        }
        return response([
            "ok"=> true,
            "data"=>$site
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
    public function withGestionnaire(Request $request){
        $site = Site::where("id_site","=",$request["id"])->with(["gestionnaire"=>function($query){
            $query->join("users","gestionnaires.id_user","=","users.id")->select("gestionnaires.id_gestionnaire","users.id","users.firstname","users.lastname","users.username","gestionnaires.email_gest")->get();
        }])->first();
        if($site){
            return response([
                "ok"=>true,
                "message"=>$site
            ],200);
        }
        return response([
            "ok"=>false,
            "message"=>"Le site n'existe pas"
        ],400);

    }
    public function rattacheA(Request $request){
        $site = Site::where("id_site","=",$request["id"])->with("rattacherA")->first();
        if($site["rattacherA"][0]->typeCollectivite=="Syndicat"){
            $site["syndicat"] = Syndicat::where("id_collectivite","=",$site["rattacherA"][0]->id_collectivite)->first();
        }
        else{
            $site["epic"]  = EPIC::where("id_collectivite","=",$site["rattacherA"][0]->id_collectivite)->first();
        }
        if($site){
            return response([
                "ok"=>true,
                "message"=>$site
            ],200);
        }
        return response([
            "ok"=>false,
            "message"=>"Le site n'existe pas"
        ],400); 
    }
    public function exploitBy(Request $request){
        $site = Site::where("id_site","=",$request["id"])->first();
        if(!$site){
            return response([
                "ok"=>false,
                "message"=>"Le site n'existe pas"
            ],400); 
        }
        $var =  false;
        $socie =  SocieteExpSite::where("id_site","=",$site->id_site)->get();
        foreach($socie as $exploit){
            if($exploit->typeExploitant=="Client"){
                $site["Client"] = SocieteExpSite::where("id_site","=",$site->id_site)->with("clients")->get();
                $var = true;
            }
            else{
                if($exploit->typeExploitant=="Societe"){
                    $site["Societe"] = SocieteExpSite::where("id_site","=",$site->id_site)->with("societes")->get();
                    $var = true;
                }
            }
        }
        if(!$var){
            $site["Client"] = null;
            $site["Societe"] = null;
        }
        return response([
            "ok"=>true,
            "message"=>$site
        ],200); 
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Site  $site
     * @return \Illuminate\Http\Response
     */
   

    /**
     * Show the form for editing the specified resource.
     *
     * @param   \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        $site = Site::with(['departement_siege:id_departement,id_departement AS value,name_departement AS label','region_siege:id_region,id_region AS value,name_region AS label'])->find($request["id_site"]);
        if($site){
            $personsData=[
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
                    "typePersonMoral"=>"Societe",
                    "name"=>"Groupe",
                    "dataIndex"=>"groupe"
                ]
            ];
            $arraySite=$site->toArray();
            $siteReturn=['siteInfo'=>$site->toArray()];
            $siteReturn['departement_siege']=$arraySite['departement_siege'];
            $siteReturn['region_siege']     = $arraySite['region_siege'];
            $siteReturn['siteInfo']['departement_siege']= isset($arraySite['departement_siege']['value']) ? $arraySite['departement_siege']['value'] : '';
            $siteReturn['siteInfo']['region_siege']= isset($arraySite['region_siege']['value']) ? $arraySite['region_siege']['value'] : '';
            $client=$site->client;
            if($client && $client->client){
                $siteReturn['siteInfo']['client']=$client->client->toArray();
                $siteReturn['siteInfo']['client'] +=$personsData[strtolower($client->typeCollectivite)];
            }
            $exploi=$site->exploitant;
            if($exploi){
                $clientSocie=$exploi->client;
                $siteReturn['siteInfo']['societe']=$clientSocie?$clientSocie->toArray():[];
                $siteReturn['siteInfo']['societe']['groupe'] = isset($siteReturn['siteInfo']['societe']['groupe']) && !empty($siteReturn['siteInfo']['societe']['groupe']) ? SocieteExploitant::getGroupeStatic($siteReturn['siteInfo']['societe']['groupe']) : [];
                $siteReturn['siteInfo']['societe'] +=!empty($personsData[strtolower($exploi->typeExploitant)])?$personsData[strtolower($exploi->typeExploitant)]:[];
            }
            $siteReturn['siteInfo']['gestionaire']=$site->gestionnaire;
            $siteReturn['siteInfo']["photos"]=$site->photos;
            $dataTech=$site->dataTech->dataTech;
            $siteReturn[$site->categorieSite]=$dataTech?$dataTech:[];
            return response([
                "ok"=>true,
                "data"=>$siteReturn
            ],200);
        }
        return response([
            "message"=>"The given data was invalid.",
            "errors"=>"Site n'existe pas"
        ],401);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Site  $site
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $siteInfo=$request['siteInfo'];
        if($siteInfo['id_site']){
            $sitetoUpdate=Site::find($siteInfo['id_site']);
            if($sitetoUpdate){
                $validator=SiteHelper::validateSiteInfo($request['siteInfo']);
                if ($validator->fails()) {
                    return response([
                        "message"=> "The given data was invalid.",
                        "level"=>0,
                        "errors"=>$validator->errors()
                    ],401);        
                }
                $sitetoUpdate->update(SiteHelper::extractSiteData(collect($siteInfo)));
                $useradmin = JWTAuth::user();
                if($useradmin->typeuser!=="Gestionnaire"){
                    $useradmin = Admin::where("id_user","=",$useradmin->id)->select("id_admin")->first();
                    $gestionaire=GestionnaireHasSite::where('id_site',$siteInfo['id_site'])->first();
                    if(!$gestionaire || $gestionaire->id_admin!=$useradmin->id_admin || $gestionaire->id_gestionnaire!=$siteInfo["gestionaire"]){
                        $gestionaire && $gestionaire->delete();
                        $geshassite =  GestionnaireHasSite::create([
                            'id_admin'=>$useradmin->id_admin,
                            'id_gestionnaire'=>$siteInfo["gestionaire"],
                            'id_site'=>$sitetoUpdate->id_site
                        ]);
                    }
                }
                $client=ClientHasSite::where('id_site',$siteInfo['id_site'])->first();
                if(!$client || $client->id_collectivite!=$siteInfo["client"]){
                    $client && $client->delete();
                    $clienthassite = ClientHasSite::create([
                        "id_site"=>$sitetoUpdate->id_site,
                        "id_collectivite"=>$siteInfo["client"]
                    ]);
                }
                $societe=SocieteExpSite::where('id_site',$siteInfo['id_site'])->first();
                if(!$societe || $societe->typeExploitant!=$siteInfo["typeExploitant"] || $societe->id_client!=$siteInfo["societe"]){
                    $societe && $societe->delete();
                    $societe = SocieteExpSite::create([
                        "typeExploitant"=>$siteInfo["typeExploitant"],
                        "id_client"=>$siteInfo["societe"],
                        "id_site"=>$sitetoUpdate->id_site
                    ]);
                }
                $techData=SiteHelper::extractTechData(collect($request[$siteInfo["categorieSite"]]),$siteInfo["categorieSite"]);
                $idtext='id_data_'.strtolower($siteInfo["categorieSite"]);
                $techClassName='App\Models\DataTechn'.$siteInfo["categorieSite"];
                $idCompare=isset($request[$siteInfo["categorieSite"]][$idtext])?$request[$siteInfo["categorieSite"]][$idtext]:null;
            
                
                $dataTech = $techClassName::where($idtext, $idCompare)->first();
                if($dataTech){
                    $dataTech->update($techData);
                }else{
                    $dataTech = $techClassName::create($dataTech);
                }

                $registredData=DataTechn::where('id_site',$sitetoUpdate->id_site)->first();
                $registredData->update([
                    "typesite"=>$siteInfo["categorieSite"],
                    "id_data_tech"=>$dataTech->{"id_data_".strtolower($siteInfo["categorieSite"])}
                ]);
                $images=$sitetoUpdate->photos;
                $ignorekey=[];
                $searchImg=array_column($request['siteInfo']['photos'],'uid');
                foreach($images as $image){
                    $keySearch=array_search($image->uid,$searchImg);
                    if($keySearch>-1){
                        $ignorekey[]=$keySearch;
                    }else{
                        $image->delete();
                    }
                } 
                foreach($request['siteInfo']['photos'] as $key=>$photo){
                    if(!in_array($key,$ignorekey)){
                        ImageSage::where("uid",$photo['uid'])
                        ->update([
                            "ref_id"=>$sitetoUpdate->id_site
                        ]);
                    }
                }
                return response([
                    "ok"=> true,
                    "data"=>"Site updated"
                ],200);
            }
        }
        return response([
            "message"=> "The given data was invalid.",
            "level"=>0,
            "errors"=>"Site n'exists pas"
        ],401);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        if(isset($request['sites']) && is_array($request['sites'])){
            $deletedLis=[];
            foreach($request['sites'] as $site_id){

                try{
                    $site = Site::find($site_id);
                    if($site){
                        $canDelete = $site->canDelete();
    
                        if($canDelete['can']){
                            Site::destroy($site_id);
                            $deletedLis[] = $site_id;
                        }else{
                            $notDeletedLis[$site_id] = $canDelete['errors'];
                        }
                    }
                }catch(\Exception $e){
                    $notDeletedLis[$site_id] = ['db.destroy-error'];
                }

                if(sizeof($request['sites']) == 1 && sizeof($notDeletedLis) == 1){
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
                'sites'=>$deletedLis,
                'not_deleted' => $notDeletedLis
            ]);
        }
        return response([
            'ok'=>true,
            'data'=>"no action"
        ]);
    }

    /**
     * Export the list of sites of the specified categorie.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request) {
        if ($request->input("category") === null || !is_string($request->input("category")) || !in_array($request->input("category"), ["UVE", "TRI", "TMB", "ISDND"]))
            $category = "UVE";
        else 
            $category = $request->input("category");
        
        ExportSites::dispatch($request->user(), "sites $category", "/sites", $category);

        return response([
            "ok" => true,
            "data" => "no action",
        ], 200);
    }

    public function export_model(Request $request) {
        if ($request->input("category") === null || !is_string($request->input("category")) || !in_array($request->input("category"), ["UVE", "TRI", "TMB", "ISDND"]))
            $category = "UVE";
        else 
            $category = $request->input("category");

        $tech_structure = [];
        $mapping = [];
        switch ($category) {
            case "UVE":
                
                $lines_count = 1;
                $valorisations_count = 1;
                $valorisation_types = ["electric" => "Electrique", "thermique" => "Thermique", "hydrogene" => "Hydrogene"];
                $tech_structure = [
                    "infos" => [
                        "type" => "child",
                        "structure" => [
                            "typeDechetRecus" => "enum_array",
                            "installationComplementair" => "enum_array",
                            "capacite" => "value",
                            "tonnageReglementaireAp" => "value",
                            "videFour" => "value"
                        ]
                    ],
                    "lines" => [
                        "type" => "list",
                        "structure" => [
                            "capacite" => "value",
                            "pci" => "value",
                            "typeFours" => "enum_array",
                            "constructeurInstallation" => "enum",
                            "typeChaudiere" => "enum_array",
                            "constructeurChaudiere" => "enum",
                            "debitVapeur" => "value",
                            "cycleVapeurPression" => "value",
                            "cycleVapeurTemp" => "value",
                            "traitementFumee" => "enum_array",
                            "equipeProcessTF" => "enum_array",
                            "reactif" => "enum_array",
                            "traitementNOX" => "enum_array",
                            "reactifNOX" => "enum_array",
                            "installationComplementair" => "enum_array",
                            "commentTraitementFumee" => "value",
                            "miseEnService" => "value",
                            "revampingDate" => "value",
                            "arretDate" => "value",
                        ],
                        "prefix" => "ligne",
                        "count" => $lines_count
                    ],
                    "valorisations" => [
                        "type" => "child",
                        "structure" => [
                            "valorisationTypes" => [
                                "type" => "map_array",
                                "values" => $valorisation_types
                            ],
                            "agregateurElectrique" => "enum",
                            "performenceEnergetique" => "value",
                            "electriciteVendue" => "value",
                            "chaleurVendue" => "value",
                            "H2Vendue" => "value",
                            "informationComplementaire" => "value",
                            "blocks" => [
                                "type" => "list",
                                "structure" => [
                                    "type" => [
                                        "type" => "map",
                                        "values" => $valorisation_types
                                    ],
                                    "name" => "value",
                                    "miseEnService" => "value",
                                    "typeEquipement" => "enum",
                                    "marqueEquipement" => "enum",
                                    "puissanceInstallee" => "value",
                                    "electriciteVendue" => "value",
                                    "RCUIndustirel" => "enum",
                                    "client" => "enum_array",
                                    "chaleurVendue" => "value",
                                    "puissanceElectrolyseur" => "value",
                                    "H2Vendue" => "value"
                                ],
                                "prefix" => "valorisation",
                                "count" => $valorisations_count
                            ]
                        ]
                    ]
                ];
                $mapping = [
                    "typeDechetRecus" => "Types de dechets recus",
                    "installationComplementair" => "Installations complémentaires",
                    "capacite" => "Capacité (t/h)",
                    "tonnageReglementaireAp" => "Tonnage réglementaire indiqué dans l'AP",
                    "videFour" => "Vide de four",
                    "valorisationTypes" => "Types valorisation",
                    "agregateurElectrique" => "Agrégateur - acheteur électricité",
                    "performenceEnergetique" => "Performance Energétique (Pe / R1)",
                    "electriciteVendue" => "Electricité vendue (MWh/a)",
                    "chaleurVendue" => "Chaleur vendue (MWh/an)",
                    "H2Vendue" => "Quantité H2 vendue (t/an)",
                    "informationComplementaire" => "Informations complémentaires",
                ]; 

                break;
            case "TRI":
                $tech_structure = [
                    "capaciteHoraire" => "value",
                    "capaciteNominale" => "value",
                    "capaciteReglementaire" => "value",
                    "dernierConstructeur" => "value",
                    "dateExtension" => "value",
                    "miseEnService" => "value",
                    "extension" => "enum",
                ];
        
                $mapping += [
                    "capaciteHoraire" => "Capacité horaire Tonnes/h",
                    "capaciteNominale" => "Capacité nominale (T/an)",
                    "capaciteReglementaire" => "Capacité réglementaire",
                    "dernierConstructeur" => "Dernier constructeur connu",
                    "dateExtension" => "Date d'extension",
                    "miseEnService" => "Date mise en service",
                    "extension" => "Extension",
                ];
                break;
            case "TMB":
                $tech_structure = [
                    "quantiteRefus" => "value",
                    "CSRProduit" => "value",
                    "envoiPreparation" => "value",
                    "tonnageAnnuel" => "value",
                    "capaciteNominal" => "value",
                    "dernierConstruct" => "value",
                    "typeInstallation" => "enum",
                    "typeDechetAccepter" => "enum_array",
                    "technologie" => "enum_array",
                    "valorisationEnergitique" => "enum_array",
                    "autreActivite" => "enum_array",
                ];

                $mapping += [
                    "quantiteRefus" => "Quantité de refus (t)",
                    "CSRProduit" => "CSR produit (t)",
                    "envoiPreparation" => "Envoi pour préparation CSR (t)",
                    "tonnageAnnuel" => "Tonnage annuel",
                    "capaciteNominal" => "Capacité nominale",
                    "dernierConstruct" => "Dernier constructeur connu",
                    "typeInstallation" => "Type d'installation",
                    "typeDechetAccepter" => "Types de déchets acceptés",
                    "technologie" => "Technologies",
                    "valorisationEnergitique" => "Valorisations energétique",
                    "autreActivite" => "Autres activités du site",
                ];
                break;
            case "ISDND":
                $yes_no_values = ["Non", "Oui"];

                $tech_structure = [
                    "capaciteNominale" => "value",
                    "capaciteRestante" => "value",
                    "capaciteReglementaire" => "value",
                    "projetExtension" => [
                        "type" => "map",
                        "values" => $yes_no_values
                    ],
                    "dateExtension" => "value",
                    "dateOuverture" => "value",
                    "dateFermeture" => "value",
                    "dateFermeturePrev" => "value",
                ];

                $mapping += [
                    "capaciteNominale" => "Capacité nominale (T/an)",
                    "capaciteRestante" => "Capacité restante",
                    "capaciteReglementaire" => "Capacité réglementaire",
                    "projetExtension" => "Projet d'extension ?",
                    "dateExtension" => "Date d'extension",
                    "dateOuverture" => "Date d'ouverture",
                    "dateFermeture" => "Date de fermeture",
                    "dateFermeturePrev" => "Date de fermeture prévisionnelle",
                ];
                break;
        }
        
        $status_values = ["VALIDATED" => "Validée / publiable", "NOT_VALIDATED" => "Non validée mais publiable", "NOT_PUBLISHED" => "Non publiable"];
        $employee_status_values = ["Inactif", "Actif"];

        $structure = [
            "denomination" => "value",
            "categorieSite" => "value",
            "adresse" => "value",
            "latitude" => "value",
            "langititude" => "value",
            "siteIntrnet" => "value",
            "telephoneStandrad" => "value",
            "anneeCreation" => "value",
            "modeGestion" => "value",
            "perdiocitRelance" => "value",
            "sinoe" => "value",
            "status" => [
                "type" => "map",
                "values" => $status_values
            ],
            "city" => "value",
            "country" => "value",
            "postcode" => "value",
            "departement_siege" => [
                "type" => "child",
                "structure" => [
                    "departement_code" => "value",
                    "name_departement" => "value",
                ],
                "prefix" => "Département - "
            ],
            "region_siege" => [
                "type" => "child",
                "structure" => [
                    "region_code" => "value",
                    "name_region" => "value",
                ],
                "prefix" => "Région - "
            ],
            "client" => [
                "type" => "child",
                "structure" => [
                    "typeCollectivite" => "value",
                    "client" => [
                        "type" => "child",
                        "structure" => [
                            "serin" => "value",
                            "dataIndex" => "ref",
                            "denomination" => "value",
                            "groupe" => "enum_array",
                            "city" => "value"
                        ]
                    ]
                ],
                "prefix" => "Collectivité - "
            ],
            "exploitant" => [
                "type" => "child",
                "structure" => [
                    "typeExploitant" => "value",
                    "client" => [
                        "type" => "child",
                        "structure" => [
                            "serin" => "value",
                            "dataIndex" => "ref",
                            "denomination" => "value",
                            "groupe" => "enum_array",
                            "city" => "value"
                        ]
                    ]
                ],
                "prefix" => "Societé - "
            ],
            "gestionnaire" => [
                "type" => "child",
                "structure" => [
                    "email" => "value",
                    "nom" => "value",
                    "prenom" => "value",
                    "status" => [
                        "type" => "map",
                        "values" => $employee_status_values
                    ]
                ],
                "prefix" => "Employé - ",
                "mapping" => [
                    "nom" => "Nom",
                    "prenom" => "Prénom",
                    "email" => "Email",
                    "status" => "Status"
                ]
            ],
            "data_tech" => [
                "type" => "child",
                "structure" => [
                    "data_tech" => [
                        "type" => "child",
                        "structure" => $tech_structure
                    ]
                ]
            ]

        ];
        
        $mapping += [
            "denomination" => "Nom",
            "categorieSite" => "Catégorie site",
            "sinoe" => "Sinoe",
            "modeGestion" => "Mode de gestion",
            "departement_code" => "Code",
            "name_departement" => "Nom",
            "region_code" => "Code",
            "name_region" => "Nom",
            "adresse" => "Adresse",
            "latitude" => "Latitude",
            "langititude" => "Longitude",
            "postcode" => "Code postal",
            "city" => "Ville",
            "country" => "Pays",
            "perdiocitRelance" => "Périodicité de relance",
            "anneeCreation" => "Année création",
            "siteIntrnet" => "Site internet",
            "telephoneStandrad" => "Tél standard",
            "status" => "Statut de la fiche",
            "typeCollectivite" => "Type",
            "typeExploitant" => "Type",
            "serin" => "Siren",
            "groupe" => "Groupe",
            "nomEpic" => "Nom",
            "nomCourt" => "Nom",
            "nomCommune" => "Nom",
            "dataIndex" => "Nom"
        ];
        return Excel::download(new ArrayExport(ExportHelper::get_headings($structure, null, $mapping)), 'sites_' . $category . '_export_model.xlsx');
    }
}