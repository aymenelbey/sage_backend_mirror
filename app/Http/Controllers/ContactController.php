<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\ContactHasPersonMoral;
use App\Models\ContactSite;
use App\Models\Collectivite;
use App\Models\SocieteExploitant;
use App\Models\PersonFunction;
use Validator;

class ContactController extends Controller
{
    const PERSONS_TYPE=[
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
                ]];
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search=$request->get('search');
        $typeJoin=$request->get('typeFilter');
        $status=$request->get('status');
        $nom=$request->get('nom');$nom=$nom?$nom:$search;
        $prenom=$request->get('prenom');$prenom=$prenom?$prenom:$search;
        $address=$request->get('address');$address=$address?$address:$search;
        $telephone=$request->get('telephone');$telephone=$telephone?$telephone:$search;
        $email=$request->get('email');$email=$email?$email:$search;
        $sort=$request->get('sort');
        $sorter=$request->get('sorter');
        $function='where';
        $pageSize=$request->get('pageSize')?$request->get('pageSize'):20;
        $contactQuery = Contact::query();
        if($nom){
            $contactQuery=$contactQuery->{$function}("nom","ILIKE","%{$nom}%");
            $function=$typeJoin=="inter"?"where":"orWhere";
        }
        if($prenom){
            $contactQuery=$contactQuery->{$function}("prenom","ILIKE","%{$prenom}%");
            $function=$typeJoin=="inter"?"where":"orWhere";
        }
        if($address){
            $contactQuery=$contactQuery->{$function}("address","ILIKE","%{$address}%");
            $function=$typeJoin=="inter"?"where":"orWhere";
        }
        if($telephone){
            $contactQuery=$contactQuery->{$function}("telephone","ILIKE","%{$telephone}%");
            $function=$typeJoin=="inter"?"where":"orWhere";
        }
        if($email){
            $contactQuery=$contactQuery->{$function}("email","ILIKE","%{$email}%");
            $function=$typeJoin=="inter"?"where":"orWhere";
        }
        if(in_array($status,['active','inactive'])){
            $contactQuery=$contactQuery->{$function}("status","=",$status=='active'?true:false);
            $function=$typeJoin=="inter"?"where":"orWhere";
        }
        if(in_array($sort,['ASC','DESC']) && in_array($sorter,["status","genre","nom","prenom","telephone","mobile","email","informations",'address'])){
           $contactQuery=$contactQuery->orderBy($sorter,$sort);
        }else{
           $contactQuery=$contactQuery->orderBy("updated_at","DESC");
        }
        $contacts=$contactQuery->paginate($pageSize);
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
        $this->validate($request, [
            "status"=>["required","boolean"],
            "genre"=>["required","in:MME,MR"],
            "nom"=>["required"],
            "prenom"=>["required"],
            "address"=>["required"],
            "linkedin"=>[],
            'telephone'=>['nullable','phone:FR'],
            "persons_moral"=>["required","array"],
        ]);
        $contact = Contact::create($request->only(['status','genre','nom','prenom','telephone','mobile','email','informations','address', 'linkedin']));
        foreach($request['persons_moral'] as $presonMorl){
            if(in_array($presonMorl['type'],['Syndicat','Epic','Commune','Societe']) && !ContactHasPersonMoral::where('idPersonMoral', $presonMorl['id_person'])->where('typePersonMoral',$presonMorl['type'])->where('id_contact',$contact->id_contact)->exists()){
                $contactCollect = ContactHasPersonMoral::create([
                    "idPersonMoral"=>$presonMorl['id_person'],
                    "typePersonMoral"=>$presonMorl['type'],
                    "id_contact"=>$contact->id_contact
                ]);
                foreach($presonMorl['fonction_person'] as $function){
                    if(!PersonFunction::where('functionPerson',$function['functionPerson'])->where('id_person',$contactCollect->id_contact_has_person_morals)->exists()){
                        PersonFunction::create([
                            "functionPerson"=>$function['functionPerson'],
                            "id_person"=>$contactCollect->id_contact_has_person_morals
                        ]);
                    }
                } 
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
        $this->validate($request,[
            "id_contact"=>["required","exists:contacts,id_contact"],
            "status"=>["required","boolean"],
            "genre"=>["required","in:MME,MR"],
            "nom"=>["required"],
            "prenom"=>["required"],
            "linkedin" => [],
            "address"=>["required"],
            'telephone'=>['nullable','phone:FR'],
            "persons_moral"=>["required","array"]
        ]);
        $cont=Contact::find($request["id_contact"])->update($request->only(["status","genre","nom","prenom","telephone","mobile","email","informations","address", "linkedin"]));
        $ignorekey=[];
        $persons=ContactHasPersonMoral::where('id_contact',$request["id_contact"])->get();
        $personSearch=array_column($request['persons_moral'],'id_person');
        foreach($persons as $person){
            $keySearch=array_search($person->idPersonMoral,$personSearch);
            if($keySearch>-1 && $person->typePersonMoral==$request['persons_moral'][$keySearch]['type']){
                $ignorekey[]=$keySearch;
                $ignFunc=[];
                $ignoreFunc=array_column($request['persons_moral'][$keySearch]['fonction_person'],'functionPerson');
                $functions=PersonFunction::where('id_person',$person->id_contact_has_person_morals)->get();
                foreach($functions as $function){
                    $keyFun=array_search($function->functionPerson,$ignoreFunc);
                    if($keyFun>-1){
                        $ignFunc []=$keyFun;
                    }else{
                        $function->delete();
                    }
                }
                foreach($request['persons_moral'][$keySearch]['fonction_person'] as $key=>$function){
                    if(!in_array($key,$ignFunc) && !PersonFunction::where('functionPerson',$function['functionPerson'])->where('id_person',$person->id_contact_has_person_morals)->exists()){
                         PersonFunction::create([
                            "functionPerson"=>$function['functionPerson'],
                            "id_person"=>$person->id_contact_has_person_morals
                        ]);
                    }
                }
            }else{
                $person->delete();
            }
        } 
        foreach($request['persons_moral'] as $key=>$person){
            if(!in_array($key,$ignorekey) && !ContactHasPersonMoral::where('idPersonMoral', $person['id_person'])->where('typePersonMoral',$person['type'])->where('id_contact',$request["id_contact"])->exists()){
                $contactCollect = ContactHasPersonMoral::create([
                    "idPersonMoral"=>$person['id_person'],
                    "typePersonMoral"=>$person['type'],
                    "id_contact"=>$request["id_contact"]
                ]);
                foreach($person['fonction_person'] as $function){
                    if(!PersonFunction::where('functionPerson',$function['functionPerson'])->where('id_person',$contactCollect->id_contact_has_person_morals)->exists()){
                        PersonFunction::create([
                            "functionPerson"=>$function['functionPerson'],
                            "id_person"=>$contactCollect->id_contact_has_person_morals
                        ]);
                    }
                } 
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
        $contact = Contact::with(['persons_moral'])->find($request["id_contact"]);
        if($contact){
            $result=$contact->toArray();
            $result['persons_moral']=$contact->persons_moral->map(function($person_moral){
                return self::PERSONS_TYPE[strtolower($person_moral->typePersonMoral)]+[
                    'id_person'=>$person_moral->idPersonMoral,
                    "id_person_moral"=>$person_moral->id_contact_has_person_morals,
                    'fonction_person'=>$person_moral->fonction_person->append('function_string')
                ]+$person_moral->person->only(['adresse','city',self::PERSONS_TYPE[strtolower($person_moral->typePersonMoral)]['dataIndex']]);
            });
            return response([
                "ok"=>true,
                "data"=>json_encode($result)
            ],200);
        }
        return response([
            "ok"=>"server",
            "errors"=>"Contact n'existe pas"
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
        $contact = Contact::with(['persons_moral'])->find($request["id_contact"]);
        if($contact){
            $result=$contact->toArray();
            $result['persons_moral']=$contact->persons_moral->map(function($person_moral){
                return self::PERSONS_TYPE[strtolower($person_moral->typePersonMoral)]+[
                    'id_person'=>$person_moral->idPersonMoral,
                    'fonction_person'=>$person_moral->fonction_person
                ]+$person_moral->person->only(['adresse','city',self::PERSONS_TYPE[strtolower($person_moral->typePersonMoral)]['dataIndex']]);
            });
            return response([
                "ok"=>true,
                "data"=>json_encode($result)
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
            'ok'=>false,
            'data'=>"no action"
        ],400);
    }

    /**
     * Remove an function of person in a contact from the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function delete_function(Request $request)
    {
        $this->validate($request,[
            'contact'=>['required','exists:contacts,id_contact'],
            'function_person'=>['required'],
            'person_moral'=>['required']
        ]);
        $person=ContactHasPersonMoral::where([
            ['id_contact_has_person_morals','=',$request['person_moral']],
            ['id_contact','=',$request['contact']]
        ])->first();
        if($person){
            $funtion=PersonFunction::where([
                ['id_person_function','=',$request['function_person']],
                ['id_person','=',$request['person_moral']]
            ])->first();
            if($funtion){
                $funtion->delete();
                return response([
                    'ok'=>true,
                    'async'=>true
                ],200);
            }
        }
        return response([
            'ok'=>false,
            'data'=>"no action"
        ],400);

    }

    /**
     * able/enable an function of person in a contact from the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function handle_function(Request $request)
    {
         $this->validate($request,[
            'contact'=>['required','exists:contacts,id_contact'],
            'function_person'=>['required'],
            'person_moral'=>['required']
        ]);
        $person=ContactHasPersonMoral::where([
            ['id_contact_has_person_morals','=',$request['person_moral']],
            ['id_contact','=',$request['contact']]
        ])->first();
        if($person){
            $funtion=PersonFunction::where([
                ['id_person_function','=',$request['function_person']],
                ['id_person','=',$request['person_moral']]
            ])->first();
            if($funtion){
                $funtion->status=!$funtion->status;
                $funtion->save();
                $funtion->append('function_string');
                return response([
                    'ok'=>true,
                    'async'=>true,
                    'data'=>$funtion
                ],200);
            }
        }
        return response([
            'ok'=>false,
            'data'=>"no action"
        ],400);
    }
}