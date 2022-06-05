<?php

namespace App\Http\Controllers;

use App\Models\EPIC;
use App\Models\Collectivite;
use App\Models\SyndicatHasEpic;
use App\Models\CompetanceDechet;
use App\Models\InfoClientHistory;
use Illuminate\Http\Request;
use App\Http\Helpers\SiteHelper;

use Illuminate\Validation\Rule;

use Validator;
use App\Rules\Siren;
use Carbon\Carbon;

class EPICController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request)
    {
        $search=$request->get('search');
        $typeJoin=$request->get('typeFilter');
        $nomEpic=$request->get('nomEpic');$nomEpic=$nomEpic?$nomEpic:$search;
        $address=$request->get('address');$address=$address?$address:$search;
        $serin=$request->get('serin');$serin=$serin?$serin:$search;
        $nombreHabitant=$request->get('nombreHabitant');
        $id_epic=$request->get('id_epic');
        $sort=$request->get('sort');
        $sorter=$request->get('sorter');
        $nature_juridique=$request->get('nature_juridique');
        $region_siege=$request->get('region_siege');
        $departement_siege=$request->get('departement_siege');
        $function='where';
        $funHas='whereHas';
        $pageSize=$request->get('pageSize')?$request->get('pageSize'):20;
        $epicQuery = EPIC::query();
        if($id_epic){
            $epicQuery=$epicQuery->{$function}("id_epic","=",$id_epic);
            $function=$typeJoin=="inter"?"where":"orWhere";
            $funHas=$typeJoin=="inter"?"whereHas":"orWhereHas";
        }
        if($nomEpic){
            $epicQuery=$epicQuery->{$function}("nomEpic","ILIKE","%{$nomEpic}%");
            $function=$typeJoin=="inter"?"where":"orWhere";
            $funHas=$typeJoin=="inter"?"whereHas":"orWhereHas";
        }
        if($address){
            $epicQuery=$epicQuery->{$function}("adresse","ILIKE","%{$address}%");
            $function=$typeJoin=="inter"?"where":"orWhere";
            $funHas=$typeJoin=="inter"?"whereHas":"orWhereHas";
        }
        if($serin){
            $epicQuery=$epicQuery->{$function}("serin","ILIKE","%{$serin}%");
            $function=$typeJoin=="inter"?"where":"orWhere";
            $funHas=$typeJoin=="inter"?"whereHas":"orWhereHas";
        }
        if($nombreHabitant){
            $epicQuery=$epicQuery->{$function}("nombreHabitant","<=",$nombreHabitant);
            $function=$typeJoin=="inter"?"where":"orWhere";
            $funHas=$typeJoin=="inter"?"whereHas":"orWhereHas";
        }
        if($nature_juridique){
            $epicQuery=$epicQuery->{$funHas}("nature_juridique",function($query)use($nature_juridique){
                $query->where('value_enum', 'ILIKE', "%{$nature_juridique}%");
            });
            $function=$typeJoin=="inter"?"where":"orWhere";
            $funHas=$typeJoin=="inter"?"whereHas":"orWhereHas";
        }
        if($region_siege){
            $epicQuery=$epicQuery->{$funHas}("region_siege",function($query)use($region_siege){
                $query->where('value_enum', 'ILIKE', "%{$region_siege}%");
            });
            $function=$typeJoin=="inter"?"where":"orWhere";
            $funHas=$typeJoin=="inter"?"whereHas":"orWhereHas";
        }
        if($departement_siege){
            $epicQuery=$epicQuery->{$funHas}("departement_siege",function($query)use($departement_siege){
                $query->where('value_enum', 'ILIKE', "%{$departement_siege}%");
            });
            $function=$typeJoin=="inter"?"where":"orWhere";
            $funHas=$typeJoin=="inter"?"whereHas":"orWhereHas";
        }
        if(in_array($sort,['ASC','DESC']) && in_array($sorter,["nomEpic","serin","adresse",'nom_court','sinoe',"siteInternet","telephoneStandard","nombreHabitant",
        "nature_juridique","departement_siege","competence_dechet","region_siege","exerciceCompetance","id_epic"])){
            $epicQuery=$epicQuery->orderBy($sorter,$sort);
        }else{
           $epicQuery=$epicQuery->orderBy("updated_at","DESC");
        }
        $epics=$epicQuery->paginate($pageSize);
        $epics->map(function($epic){
           $epic->withEnums();
        });
        return response([
            "ok"=>true,
            "data"=> $epics
        ],200);
    }
    public function showWithCommune(Request $request){
        $epic = EPIC::where("id_epic","=",$request["id"])->with("communes")->first();
        if($epic){
            return response([
                "ok"=>true,
                "message"=>$epic
            ],200);
        }
        return response([
            "ok"=>true,
            "message"=>"Epic n'exite pas"
        ],400);
    }
    public function showWithSyndicat(Request $request){
        $epic = EPIC::where("id_epic","=",$request["id"])->with("syndicats")->first();
        if($epic){
            return response([
                "ok"=>true,
                "message"=>$epic
            ],200);
        }
        return response([
            "ok"=>true,
            "message"=>"Epic n'exite pas"
        ],400);

    }
    public function updateCommune(Request $request){
        $rules = [
            "id_epic"=>["required","exists:e_p_i_c_s"],
            "id_communes"=>["required"]
        ];
        $validator = Validator::make($request->all(),$rules);
        if($validator->fails()){
            return response([
                "ok"=>false,
                "message"=>$validator->errors()
            ],400);
        }
        //$array_communes = [];
        foreach($request["id_communes"] as $id){
            $val = Validator::make(["id_commune"=>$id],["id_commune"=>"exists:communes"],["exists"=>"Commune n'existe pas"]);
            if($val->fails()){
                return response([
                    "ok"=>false,
                    "message"=>$val->errors()
                ],400);
            }
        }
        foreach($request["id_communes"] as $id){
            $upd =  Commune::where("id_commune","=",$id)->update(["id_epic"=>$request["id_epic"]]);
        }
        return response([
            "ok"=>true,
            "message"=>"Les communes sont associés avec succés"
        ],200);
    }
    public function updateSyndicat(Request $request){
        $rules = [
            "id_epic"=>["required","exists:e_p_i_c_s"],
            "id_syndicat"=>["required"]
        ];
        $validator = Validator::make($request->all(),$rules);
        if($validator->fails()){
            return response([
                "ok"=>false,
                "message"=>$validator->errors()
            ],400);
        }
        $array_syndicat = [];
        foreach($request["id_syndicat"] as $id){
            $val = Validator::make(["id_syndicat"=>$id],["id_syndicat"=>"exists:syndicats"],["exists"=>"Syndicat n'existe pas"]);
            if($val->fails()){
                return response([
                    "ok"=>false,
                    "message"=>$val->errors()
                ],400);
            }
            $temp = [
                "id_epic"=>$id,
                "id_syndicat"=>$id,
                "created_at"=>Carbon::now(),
                "updated_at"=>Carbon::now()
            ];
            array_push($array_syndicat,$temp);
        }
        $syndic = SyndicatHasEpic::insert($array_syndicat);
        return response([
            "ok"=>true,
            "message"=>"Données insérées avec succées"
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
            "nomEpic"=>["required","string"],
            'nom_court'=>["required"],
            "sinoe"=>['required', 'unique:epics'],
            "city"=>["required"],
            "country"=>['required'],
            "postcode"=>['required'],
            "adresse"=>['required'],
            "serin"=> ["required","numeric", new Siren],
            "siret"=> ["required","numeric", new Siren],
            'nature_juridique'=>["required","exists:enemurations,id_enemuration"],
            'departement_siege'=>["required","exists:departements,id_departement"],
            'region_siege'=>["required","exists:regions,id_region"],
            "competance_exercee"=>["array"],
            "competance_delegue"=>["array"],
            'telephoneStandard'=>['nullable','phone:FR'],
            "status" => ['required']
        ],[],[
            'serin'=>'Siren'
        ]);
        $client = Collectivite::create([
            "typeCollectivite"=>"EPIC"
        ]);
        $epic = EPIC::create($request->only(["nomEpic","serin","siret","nom_court","sinoe","adresse","lat","lang","siteInternet","telephoneStandard","nombreHabitant","logo","nature_juridique","departement_siege","region_siege","city","country","postcode", "status"])+['id_collectivite'=>$client->id_collectivite,'date_enter'=>Carbon::now()]);
        foreach($request->competance_exercee as $competance){
            if($competance['code'] && $competance['competence_dechet']){
                CompetanceDechet::create([
                    'code'=>$competance['code'],
                    'start_date'=> SiteHelper::formatDateIfNotNull($competance['start_date']),
                    'end_date'=> SiteHelper::formatDateIfNotNull($competance['end_date']),
                    'comment'=>$competance['comment'],
                    'owner_competance'=>$epic->id_epic,
                    'owner_type'=>"EPIC",
                    'competence_dechet'=>$competance['competence_dechet']
                ]);
            }
        };
        foreach($request->competance_delegue as $competance){
            if($competance['code'] && $competance['competence_dechet'] && $competance['delegue_competance']){
                CompetanceDechet::create([
                    'code'=>$competance['code'],
                    'start_date'=> SiteHelper::formatDateIfNotNull($competance['start_date']),
                    'end_date'=> SiteHelper::formatDateIfNotNull($competance['end_date']),
                    'comment'=>$competance['comment'],
                    'owner_competance'=>$epic->id_epic,
                    'owner_type'=>"EPIC",
                    'competence_dechet'=>$competance['competence_dechet'],
                    'delegue_competance'=>$competance['delegue_competance']['id_person'],
                    'delegue_type'=>$competance['delegue_competance']['typePersonMoral']
                ]);
            }
        };
        return response([
            "ok"=>true,
            "data"=>$epic
        ],200);

    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\EPIC  $ePIC
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $this->validate($request,[
            "id_epic"=>["required","exists:epics"],
            "nomEpic"=>["required","string"],
            "sinoe" => ['required', Rule::unique('epics', 'sinoe')->ignore($request["id_epic"], 'id_epic')],
            "serin"=> ["required","numeric", new Siren],
            "siret"=> ["required","numeric", new Siren],
            'nom_court'=>["required"],
            'nature_juridique'=>["required","exists:enemurations,id_enemuration"],
            'region_siege'=>["required","exists:regions,id_region"],
            "competance_exercee"=>["array"],
            "competance_delegue"=>["array"],
            'telephoneStandard'=>['nullable','phone:FR'],
            "status" => ['required']
        ],[],[
            'serin'=>'Siren'
        ]);
        $epic = EPIC::find($request["id_epic"]);
        $moreItems=[];
        if($epic->nombreHabitant!=$request['nombreHabitant']){
            $moreItems=[
                'nombreHabitant'=>$request['nombreHabitant'],
                'date_enter'=>Carbon::now()
            ];
            InfoClientHistory::customCreate([
                'id_reference'=>$epic->id_epic,
                'referenced_table'=>"Epic",
                'referenced_column'=>'nombreHabitant',
                'date_reference'=>$epic->date_enter,
                'prev_value'=>$epic->nombreHabitant
            ]);
        }
        $moreItems['logo'] = isset($request['logo']) ? $request['logo']: null;
        
        $epic->update($request->only(["nomEpic", "nom_court","sinoe","serin","siret","adresse","lat","lang","siteInternet","telephoneStandard","nature_juridique","departement_siege","region_siege","city","country","postcode", "status"])+$moreItems);
        $competanceExercee=$epic->competance_exercee->toArray();
        $searchedComp=array_column($competanceExercee,'id_competance_dechet');
        foreach($request->competance_exercee as $competance){
            if(!empty($competance['id_competance_dechet'])){
                $indexItem=array_search($competance['id_competance_dechet'],$searchedComp);
                if($indexItem>-1){
                    if($competance['code'] && $competance['competence_dechet']){
                        CompetanceDechet::where('id_competance_dechet',$competance['id_competance_dechet'])->update([
                            'code'=>$competance['code'],
                            'start_date'=> SiteHelper::formatDateIfNotNull($competance['start_date']),
                            'end_date'=> SiteHelper::formatDateIfNotNull($competance['end_date']),
                            'comment'=>$competance['comment'],
                            'competence_dechet'=>$competance['competence_dechet']
                        ]);
                    }
                }
            }else{
                if($competance['code'] && $competance['competence_dechet']){
                    CompetanceDechet::create([
                        'code'=>$competance['code'],
                        'start_date'=> SiteHelper::formatDateIfNotNull($competance['start_date']),
                        'end_date'=> SiteHelper::formatDateIfNotNull($competance['end_date']),
                        'comment'=>$competance['comment'],
                        'owner_competance'=>$epic->id_epic,
                        'owner_type'=>"EPIC",
                        'competence_dechet'=>$competance['competence_dechet']
                    ]);
                }
            }
        };
        $toBeDeleted=array_column($request['competance_exercee'],'id_competance_dechet');
        foreach($epic->competance_exercee as $compe){
            $indexItem=array_search($compe['id_competance_dechet'],$toBeDeleted);
            if(!($indexItem>-1)){
                $compe->delete();
            }
        }
        /**** delegue part */
        $competanceExercee=$epic->competance_delegue->toArray();
        $searchedComp=array_column($competanceExercee,'id_competance_dechet');
        foreach($request->competance_delegue as $competance){
            if(!empty($competance['id_competance_dechet'])){
                $indexItem=array_search($competance['id_competance_dechet'],$searchedComp);
                if($indexItem>-1){
                    if($competance['code'] && $competance['competence_dechet'] && $competance['delegue_competance']){
                        CompetanceDechet::where('id_competance_dechet',$competance['id_competance_dechet'])->update([
                            'code'=>$competance['code'],
                            'start_date'=> SiteHelper::formatDateIfNotNull($competance['start_date']),
                            'end_date'=> SiteHelper::formatDateIfNotNull($competance['end_date']),
                            'comment'=>$competance['comment'],
                            'competence_dechet'=>$competance['competence_dechet'],
                            'delegue_competance'=>$competance['delegue_competance']['id_person'],
                            'delegue_type'=>$competance['delegue_competance']['typePersonMoral']
                        ]);
                    }
                }
            }else{
                if($competance['code'] && $competance['competence_dechet'] && $competance['delegue_competance']){
                    CompetanceDechet::create([
                        'code'=>$competance['code'],
                        'start_date'=> SiteHelper::formatDateIfNotNull($competance['start_date']),
                        'end_date'=> SiteHelper::formatDateIfNotNull($competance['end_date']),
                        'comment'=>$competance['comment'],
                        'owner_competance'=>$epic->id_epic,
                        'owner_type'=>"EPIC",
                        'competence_dechet'=>$competance['competence_dechet'],
                        'delegue_competance'=>$competance['delegue_competance']['id_person'],
                        'delegue_type'=>$competance['delegue_competance']['typePersonMoral']
                    ]);
                }
            }
        };
        $toBeDeleted=array_column($request['competance_delegue'],'id_competance_dechet');
        foreach($epic->competance_delegue as $compe){
            $indexItem=array_search($compe['id_competance_dechet'],$toBeDeleted);
            if(!($indexItem>-1)){
                $compe->delete();
            }
        }
        return response([
            "ok"=>true,
            "data"=>$epic
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
     * @param  \App\Models\EPIC  $ePIC
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        if(!empty($request['idepic'])){
            $idEpic=$request['idepic'];
            $epic=EPIC::with(['communes','syndicat','updated_by', 'contacts','logo','competance_exercee','competance_delegue','competance_recu', 'sites', 'status_updated_by'])->find($idEpic);
            $epic->withEnums();
            $epic->effectif_history = $epic->effectif_history()->get();
            
            $epic['files'] = $epic->files()->get();
            foreach($epic['files'] as $file){
                $file->entity = $file->entity(); 
                $file->path = $file->getPath();
            }
            
            $epic=$epic->toArray();
            $tmpArray=array_merge($epic['competance_exercee'],$epic['competance_recu']);
            unset($epic['competance_recu']);unset($epic['competance_exercee']);
            $epic['competance_exercee']=$tmpArray;
            if(!empty($epic["logo"][0])){
                $epic["logo"]=$epic["logo"][0]["url"];
            }
            return response([
                'ok'=>true,
                'data'=>$epic
            ],200);
        }
        return response([
            'ok'=>'server',
            'errors'=>'Aucune epic disponible'
        ],400);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\EPIC  $ePIC
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        if(!empty($request['idepic'])){
            $idEpic=$request['idepic'];
            $epic=EPIC::with(['syndicat','logo','departement_siege:id_departement,id_departement AS value,name_departement AS label','region_siege:id_region,id_region AS value,name_region AS label'])->find($idEpic);
            $returnedData=$epic->toArray();
            $returnedData['competances']=[
                'exercee'=>$epic->competance_exercee,
                'delegue'=>$epic->competance_delegue
            ];
            return response([
                'ok'=>true,
                'data'=>json_encode($returnedData)
            ],200);
        }
        return response([
            'ok'=>'server',
            'errors'=>'Aucune epic disponible'
        ],400);
    }



    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\EPIC  $ePIC
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        if(isset($request['epics']) && is_array($request['epics'])){
            $deletedLis=[];
            foreach($request['epics'] as $epic){
                $epicObj=EPIC::find($epic);
                $collect=Collectivite::find($epicObj->id_collectivite);
                if($epicObj && $collect){
                    $deletedLis [] = $epic;
                    $collect->delete();
                    $epicObj->delete();
                }
            }
            return response([
                'ok'=>true,
                'data'=>"async",
                'epics'=>$deletedLis
            ]);
        }
        return response([
            'ok'=>true,
            'data'=>"no action"
        ]);
    }
}