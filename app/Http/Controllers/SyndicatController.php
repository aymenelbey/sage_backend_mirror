<?php

namespace App\Http\Controllers;

use App\Http\Helpers\SiteHelper;
use App\Models\Syndicat;
use App\Models\Collectivite;
use App\Models\SyndicatHasEpic;
use App\Models\CompetanceDechet;
use App\Models\InfoClientHistory;
use Illuminate\Http\Request;
use Validator;
use App\Rules\Siren;
use Carbon\Carbon;

use Illuminate\Validation\Rule;


class SyndicatController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request){
        $search=$request->get('search');
        $typeJoin=$request->get('typeFilter');
        $nomCourt=$request->get('nomCourt');$nomCourt=$nomCourt?$nomCourt:$search;
        $address=$request->get('address');$address=$address?$address:$search;
        $denomination=$request->get('denomination');$denomination=$denomination?$denomination:$search;
        $serin=$request->get('serin');$serin=$serin?$serin:$search;
        $nature_juridique=$request->get('nature_juridique');
        $region_siege=$request->get('region_siege');
        $departement_siege=$request->get('departement_siege');
        $competence_dechet=$request->get('competence_dechet');
        $amobe=$request->get('amobe');
        $sort=$request->get('sort');
        $sorter=$request->get('sorter');
        $function='where';
        $funHas='whereHas';
        $pageSize=$request->get('pageSize')?$request->get('pageSize'):20;
        $syndicatQuery = Syndicat::query();
        if($nomCourt){
            $syndicatQuery=$syndicatQuery->{$function}("nomCourt","ILIKE","%{$nomCourt}%");
            $function=$typeJoin=="inter"?"where":"orWhere";
            $funHas=$typeJoin=="inter"?"whereHas":"orWhereHas";
        }
        if($address){
            $syndicatQuery=$syndicatQuery->{$function}("adresse","ILIKE","%{$address}%");
            $function=$typeJoin=="inter"?"where":"orWhere";
            $funHas=$typeJoin=="inter"?"whereHas":"orWhereHas";
        }
        if($denomination){
            $syndicatQuery=$syndicatQuery->{$function}("denominationLegale","ILIKE","%{$denomination}%");
           $function=$typeJoin=="inter"?"where":"orWhere";
            $funHas=$typeJoin=="inter"?"whereHas":"orWhereHas";
        }
        if($serin){
            $syndicatQuery=$syndicatQuery->{$function}("serin","ILIKE","%{$serin}%");
            $function=$typeJoin=="inter"?"where":"orWhere";
            $funHas=$typeJoin=="inter"?"whereHas":"orWhereHas";
        }
        if($nature_juridique){
            $syndicatQuery=$syndicatQuery->{$funHas}("nature_juridique",function($query)use($nature_juridique){
                $query->where('value_enum', 'ILIKE', "%{$nature_juridique}%");
            });
            $function=$typeJoin=="inter"?"where":"orWhere";
            $funHas=$typeJoin=="inter"?"whereHas":"orWhereHas";
        }
        if($region_siege){
            $syndicatQuery=$syndicatQuery->{$funHas}("region_siege",function($query)use($region_siege){
                $query->where('value_enum', 'ILIKE', "%{$region_siege}%");
            });
            $function=$typeJoin=="inter"?"where":"orWhere";
            $funHas=$typeJoin=="inter"?"whereHas":"orWhereHas";
        }
        if($departement_siege){
            $syndicatQuery=$syndicatQuery->{$funHas}("departement_siege",function($query)use($departement_siege){
                $query->where('value_enum', 'ILIKE', "%{$departement_siege}%");
            });
            $function=$typeJoin=="inter"?"where":"orWhere";
            $funHas=$typeJoin=="inter"?"whereHas":"orWhereHas";
        }
        if($competence_dechet){
            $syndicatQuery=$syndicatQuery->{$funHas}("competence_dechet",function($query)use($competence_dechet){
                $query->where('value_enum', 'ILIKE', "%{$competence_dechet}%");
            });
            $function=$typeJoin=="inter"?"where":"orWhere";
            $funHas=$typeJoin=="inter"?"whereHas":"orWhereHas";
        }
        if($amobe){
            $syndicatQuery=$syndicatQuery->{$funHas}("amobe",function($query)use($amobe){
                $query->where('value_enum', 'ILIKE', "%{$amobe}%");
            });
            $function=$typeJoin=="inter"?"where":"orWhere";
            $funHas=$typeJoin=="inter"?"whereHas":"orWhereHas";
        }
        if(in_array($sort,['ASC','DESC']) && in_array($sorter,["nomCourt","denominationLegale","serin","adresse","siteInternet","telephoneStandard","nombreHabitant",'amobe','nature_juridique','departement_siege','competence_dechet','region_siege',"email","sinoe"])){
            $syndicatQuery=$syndicatQuery->orderBy($sorter,$sort);
        }else{
            $syndicatQuery=$syndicatQuery->orderBy("updated_at","DESC");
        }
        $syndicats=$syndicatQuery->paginate($pageSize);
        $syndicats->map(function($syndicat){
           $syndicat->withEnums();
        });
        return response([
            "ok"=>true,
            "data"=> $syndicats
        ],200);
    }
    public function show(Request $request){
        if(!empty($request['idSyndicat'])){
            $idSyndicat=$request['idSyndicat'];
            $syndicat=Syndicat::with(['contacts.persons_moral', 'competance_exercee','competance_delegue','competance_recu','sites','logo','ged_rapport', 'epics', 'updated_by'])->find($idSyndicat);
            $syndicat->withEnums();
            $syndicat->epics->map(function($epic){
                $epic->withEnums();
            });
            $syndicat=$syndicat->toArray();
            $tmpArray=array_merge($syndicat['competance_exercee'],$syndicat['competance_recu']);
            unset($syndicat['competance_recu']);unset($syndicat['competance_exercee']);
            $syndicat['competance_exercee']=$tmpArray;
            if(!empty($syndicat["logo"][0])){
                $syndicat["logo"]=$syndicat["logo"][0]["url"];
            }
            if(!empty($syndicat["ged_rapport"][0])){
                $syndicat["ged_rapport"]=$syndicat["ged_rapport"][0];
            }
            return response([
                'ok'=>true,
                'data'=>$syndicat
            ],200);
        }
        return response([
            'ok'=>'server',
            'errors'=>'Aucune syndicat disponible'
        ],400);
    }
    public function updateEpic(Request $request){
        $validator = Validator::make($request->all(),[
            "id_syndicat"=>"exists:syndicats",
            "epics"=>"required"
        ],[
            "required"=>":attribute est obligatoire",
            "exists"=>":attribute n'existe pas dans la list des syndicats"
        ]);
        if($validator->fails()){
            return response([
                "ok"=>false,
                "message"=>$validator->errors()
            ],400);
        }
        if(sizeof($request["epics"])>0){
            foreach($request["epics"] as $id){
                $validator = Validator::make(["id_epic"=>$id],["id_epic"=>"exists:epics"],["exists"=>"verifier que les epics existes"]);
                if($validator->fails()){
                    return response([
                        "ok"=>false,
                        "message"=> $validator->errors()
                    ],400);
                }
            }
            $array_syndica = [];
            foreach($request["epics"] as $id){
                $temp = [
                    "id_epic"=>$id,
                    "id_syndicat"=>$request["id_syndicat"],
                    "created_at"=>Carbon::now(),
                    "updated_at"=>Carbon::now()
                ];
                array_push($array_syndica,$temp);
            }
            $syndic = SyndicatHasEpic::insert($array_syndica);
        }
        return response([
            "ok"=>true,
            "message"=>$syndic
        ]);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request){
        $this->validate($request,[
            "nomCourt"=>["required","string"],
            "serin"=> ["required","numeric", new Siren],
            "sinoe"=>["required", "unique:syndicats"],
            "email"=>["nullable","email"],
            "logo"=>["nullable","uuid","exists:image_sages,uid"],
            "ged_rapport"=>["nullable","uuid","exists:image_sages,uid"],
            "denominationLegale"=>["required","string"],
            'nature_juridique'=>["required","exists:enemurations,id_enemuration"],
            'departement_siege'=>["required","exists:departements,id_departement"],
            'region_siege'=>["required","exists:regions,id_region"],
            'adresse'=>['required'],
            "city"=>["required"],
            "country"=>['required'],
            "postcode"=>['required'],
            "competance_exercee"=>["array"],
            "competance_delegue"=>["array"],
            'telephoneStandard'=>['nullable','phone:FR']
        ],[],
            ['serin'=>'Siret']
        );
        $client = Collectivite::create([
            "typeCollectivite"=>"Syndicat"
        ]);
        $syndicat = Syndicat::create($request->only(["nomCourt","denominationLegale","serin","adresse",'lat','lang',"siteInternet","telephoneStandard","nombreHabitant","logo","ged_rapport",'amobe','nature_juridique','departement_siege','region_siege',"email","sinoe","city","country","postcode"])+['id_collectivite'=>$client->id_collectivite,'date_enter'=>Carbon::now()]);
        foreach($request->competance_exercee as $competance){
            if($competance['code'] && $competance['competence_dechet']){
                CompetanceDechet::create([
                    'code'=>$competance['code'],
                    'start_date'=> SiteHelper::formatDateIfNotNull($competance['start_date']),
                    'end_date'=> SiteHelper::formatDateIfNotNull($competance['end_date']),
                    'comment'=>$competance['comment'],
                    'owner_competance'=>$syndicat->id_syndicat,
                    'owner_type'=>"Syndicat",
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
                    'owner_competance'=>$syndicat->id_syndicat,
                    'owner_type'=>"Syndicat",
                    'competence_dechet'=>$competance['competence_dechet'],
                    'delegue_competance'=>$competance['delegue_competance']['id_person'],
                    'delegue_type'=>$competance['delegue_competance']['typePersonMoral']
                ]);
            }
        };
        return response([
            "ok"=>true,
            "data"=>$syndicat
        ],200);

    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Syndicat  $syndicat
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request){
        $this->validate($request,[
            "id_syndicat"=>["required","exists:syndicats"],
            "nomCourt"=>["required","string"],
            "serin"=> ["required","numeric", new Siren],
            "sinoe" => ["required", Rule::unique('syndicats', 'sinoe')->ignore($request["id_syndicat"], 'id_syndicat')],
            "email"=>["nullable","email"],
            "logo"=>["nullable","uuid","exists:image_sages,uid"],
            "ged_rapport"=>["nullable","uuid","exists:image_sages,uid"],
            "denominationLegale"=>["required","string"],
            'nature_juridique'=>["required","exists:enemurations,id_enemuration"],
            'departement_siege'=>["required","exists:departements,id_departement"],
            'region_siege'=>["required","exists:regions,id_region"],
            'adresse'=>['required'],
            "competance_exercee"=>["array"],
            "competance_delegue"=>["array"],
            'telephoneStandard'=>['nullable','phone:FR']
        ],[],
            ['serin'=>'Siret']
        );
        $syndicat=Syndicat::find($request['id_syndicat']);
        $moreItems=[];
        if($syndicat->nombreHabitant!=$request['nombreHabitant']){
            $moreItems=[
                'nombreHabitant'=>$request['nombreHabitant'],
                'date_enter'=>Carbon::now()
            ];
            InfoClientHistory::customCreate([
                'id_reference'=>$syndicat->id_syndicat,
                'referenced_table'=>"Syndicat",
                'referenced_column'=>'nombreHabitant',
                'date_reference'=>$syndicat->date_enter,
                'prev_value'=>$syndicat->nombreHabitant
            ]);
        }
        $moreItems=[
            'logo'=>isset($request['logo'])?$request['logo']:null
        ];
        $syndicat->update($request->only(["nomCourt","denominationLegale","serin","adresse",'lat','lang',"siteInternet","telephoneStandard","ged_rapport",'amobe','nature_juridique','departement_siege','region_siege',"email","sinoe","city","country","postcode"])+$moreItems);
        $competanceExercee=$syndicat->competance_exercee->toArray();
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
                        'owner_competance'=>$syndicat->id_syndicat,
                        'owner_type'=>"Syndicat",
                        'competence_dechet'=>$competance['competence_dechet']
                    ]);
                }
            }
        };
        $toBeDeleted=array_column($request['competance_exercee'],'id_competance_dechet');
        foreach($syndicat->competance_exercee as $compe){
            $indexItem=array_search($compe['id_competance_dechet'],$toBeDeleted);
            if(!($indexItem>-1)){
                $compe->delete();
            }
        }
        /**** delegue part */
        $competanceExercee=$syndicat->competance_delegue->toArray();
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
                        'owner_competance'=>$syndicat->id_syndicat,
                        'owner_type'=>"Syndicat",
                        'competence_dechet'=>$competance['competence_dechet'],
                        'delegue_competance'=>$competance['delegue_competance']['id_person'],
                        'delegue_type'=>$competance['delegue_competance']['typePersonMoral']
                    ]);
                }
            }
        };
        $toBeDeleted=array_column($request['competance_delegue'],'id_competance_dechet');
        foreach($syndicat->competance_delegue as $compe){
            $indexItem=array_search($compe['id_competance_dechet'],$toBeDeleted);
            if(!($indexItem>-1)){
                $compe->delete();
            }
        }
        return response([
            "ok"=>true,
            "data"=>$syndicat
        ],200);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Syndicat  $syndicat
     * @return \Illuminate\Http\Response
     */


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        $syndicat=Syndicat::with(['logo','ged_rapport','departement_siege:id_departement,id_departement AS value,name_departement AS label','region_siege:id_region,id_region AS value,name_region AS label'])->find($request['idSyndicat']);
        if($syndicat){
            $returnedData=$syndicat->toArray();
            $returnedData['competances']=[
                'exercee'=>$syndicat->competance_exercee,
                'delegue'=>$syndicat->competance_delegue
            ];
            return response([
                'ok'=>true,
                "data"=>json_encode($returnedData)
            ]);
        }
        return response([
            'ok'=>false,
            "data"=>"Syndicat not found"
        ]);
    }



    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Syndicat  $syndicat
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        if(isset($request['syndicats']) && is_array($request['syndicats'])){
            $deletedLis=[];
            foreach($request['syndicats'] as $syndicat){
                $syndica=Syndicat::find($syndicat);
                if($syndica){
                    $deletedLis [] = $syndicat;
                    $syndica->delete();
                }
            }
            return response([
                'ok'=>true,
                'data'=>"async",
                'syndicats'=>$deletedLis
            ]);
        }
        return response([
            'ok'=>true,
            'data'=>"no action"
        ]);
    }
}