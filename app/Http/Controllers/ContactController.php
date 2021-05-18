<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\ContactHasPersonMoral;
use App\Models\ContactSite;
use App\Models\Collectivite;
use App\Models\SocieteExploitant;
use Validator;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $nom=$request->get('nom');
        $prenom=$request->get('prenom');
        $address=$request->get('address');
        $function='where';
        $pageSize=$request->get('pageSize')?$request->get('pageSize'):10;
        $contactQuery = Contact::query();
        if($nom){
            $contactQuery=$contactQuery->{$function}("nom","ILIKE","%{$nom}%");
            $function='orWhere';
        }
        if($prenom){
            $contactQuery=$contactQuery->{$function}("prenom","ILIKE","%{$prenom}%");
            $function='orWhere';
        }
        if($address){
            $contactQuery=$contactQuery->{$function}("adresse","ILIKE","%{$address}%");
            $function='orWhere';
        }
        $contacts=$contactQuery->orderBy("created_at","DESC")
        ->paginate($pageSize);
        return response([
            "ok"=>true,
            "data"=>$contacts
        ],200);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request){
        $rules = [
            "status"=>["required","boolean"],
            "genre"=>["required","in:MME,MR"],
            "nom"=>["required"],
            "prenom"=>["required"],
            "address"=>["required"],
            "personalMoral"=>["required","array"],
        ];
        $message = [
            "required"=>":attribute est obligatoire",
            "in"=>":attribute doit être dans la list :values",
            "personalMoral.size"=>"presone moral ne respect pas la taille correspondant"
        ];
        $validator = Validator::make($request->all(),$rules,$message);
        if($validator->fails()){
            return response([
                "ok"=>"server",
                "errors"=>$validator->errors()
            ],400);
        }
        $contact = Contact::create([
            "status"=>$request["status"],
            "genre"=>$request["genre"],
            "nom"=>$request["nom"],
            "prenom"=>$request["prenom"],
            "telephone1"=>isset($request["telephone1"])?$request["telephone1"]:null,
            "telephone2"=>isset($request["telephone2"])?$request["telephone2"]:null,
            "mobile1"=>isset($request["mobile1"])?$request["mobile1"]:null,
            "mobile2"=>isset($request["mobile2"])?$request["mobile2"]:null,
            "email"=>isset($request["email"])?$request["email"]:null,
            "informations"=>isset($request["informations"])?$request["informations"]:null,
            "address"=>isset($request["address"])?$request["address"]:null,
        ]);
        foreach($request['personalMoral'] as $presonMorl){
            if(in_array($presonMorl['type'],['Syndicat','Epic','Commune','Societe'])){
                $contactCollect = ContactHasPersonMoral::create([
                    "idPersonMoral"=>$presonMorl['id_person'],
                    "typePersonMoral"=>$presonMorl['type'],
                    "id_contact"=>$contact->id_contact,
                    "function"=>$presonMorl['fonctionPerson']
                ]);
            }
        }
        return response([
            "ok"=>true,
            "data"=>$contact
        ],200);

    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Contact  $contact
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request){
        $cont = Contact::find($request["id_contact"]);
        if(!$cont)
            return response([
                "ok"=>"server",
                "errors"=>"Contact n'existe pas"
            ],400);
        $rules = [
            "status"=>["required","boolean"],
            "genre"=>["required","in:MME,MR"],
            "nom"=>["required"],
            "prenom"=>["required"],
            "address"=>["required"],
            "personalMoral"=>["required","array"],
        ];
        $message = [
            "required"=>":attribute est obligatoire",
            "in"=>":attribute doit être dans la list :values",
            "personalMoral.size"=>"presone moral ne respect pas la taille correspondant"
        ];
        $validator = Validator::make($request->all(),$rules,$message);
        if($validator->fails()){
            return response([
                "ok"=>"server",
                "errors"=>$validator->errors()
            ],400);
        }
        $cont->update(collect($request)->only(["status","genre","nom","prenom","telephone1","telephone2","mobile1","mobile2","email","informations","address"])->toArray());
        $ignorekey=[];
        $persons=ContactHasPersonMoral::where('id_contact',$request["id_contact"])->get();
        $personSearch=array_column($request['personalMoral'],'id_person');
        foreach($persons as $person){
            $keySearch=array_search($person->idPersonMoral,$personSearch);
            if($keySearch>-1 && $person->function==$request['personalMoral'][$keySearch]['fonctionPerson'] && $person->typePersonMoral==$request['personalMoral'][$keySearch]['type']){
                $ignorekey[]=$keySearch;
            }else{
                $person->delete();
            }
        } 
        foreach($request['personalMoral'] as $key=>$person){
            if(!in_array($key,$ignorekey)){
                $contactCollect = ContactHasPersonMoral::create([
                    "idPersonMoral"=>$person['id_person'],
                    "typePersonMoral"=>$person['type'],
                    "id_contact"=>$request["id_contact"],
                    "function"=>$person['fonctionPerson']
                ]);
            }
        }
        return response([
            "ok"=>true,
            "data"=>"Contact bien modifier"
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
        $contact=Contact::with(['personsMoral'=>function($query){
            $query->with('person');
        }])->find($request['id_contact']);
        return response([
            'ok'=>true,
            'data'=>$contact
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        $contact = Contact::find($request["id_contact"]);
        if($contact){
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
            $cnt=$contact->toArray();
            $cnt['personalMoral']=[];
            $persons=$contact->personsMoral;
            foreach($persons as $person){
                $tmpArray=[];
                $indexLower=$personsData[strtolower($person->typePersonMoral)];
                $tmpArray+=$indexLower;
                $client=$person->person;
                if($client){
                    $tmpArray[$indexLower['dataIndex']]=$client[$indexLower['dataIndex']];
                    $tmpArray["adresse"]=$client->adresse;
                    $tmpArray["id_person"]=$person->idPersonMoral;
                    $tmpArray["fonctionPerson"]=$person->function;
                    $cnt['personalMoral'] [] =$tmpArray;
                }
            }
            return response([
                "ok"=>true,
                "data"=>$cnt
            ],200);
        }
        return response([
            "ok"=>"server",
            "errors"=>"Contact n'existe pas"
        ],400);
    }

    

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        if(isset($request['contacts']) && is_array($request['contacts'])){
            $deletedLis=[];
            foreach($request['contacts'] as $contact){
                $contactObj=Contact::find($contact);
                if($contactObj){
                    $deletedLis [] = $contact;
                    $contactObj->delete();
                }
            }
            return response([
                'ok'=>true,
                'data'=>"async",
                'contacts'=>$deletedLis
            ]);
        }
        return response([
            'ok'=>true,
            'data'=>"no action"
        ]);
    }
}