<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Rules\Siren;
use App\Rules\Siret;
use Illuminate\Validation\Rule;
use Validator;
use App\Models\Commune;
use App\Models\Site;
use App\Models\Collectivite;
use Carbon\Carbon;
use Excel;
use App\Exports\CollectionsExport;
use App\Exports\SitesExport;

class TestController extends Controller
{
    function export(Request $request) {
        //return Excel::download(new SitesExport(Site::with(["dataTech", "contracts", "gestionnaire", "client", "exploitant"])->get()), 'test.xlsx');
        return Excel::download(new SitesExport(Site::all()), 'test.xlsx');
    }

    function export2(Request $request) {
        return Site::with(["dataTech", "contracts", "gestionnaire", "client", "exploitant"])->get()->toArray();
    }

    function export1(Request $request) {
        return Excel::download(new CollectionsExport(Site::with("dataTech", "contracts", "gestionnaire", "client", "exploitant")->get()->toArray()), 'test.xlsx');
    }

    function test1(Request $request) {
        $this->validate($request,[
            "serin"=> ["required","numeric", new Siren],
            "siret"=> ["numeric", "unique:communes,siret", new Siret],
        ],[],[
            'serin'=>'Siren',
        ]);

        return response()->json(["message" => "It works!"]);
    }

    public function create(Request $request){
        $this->validate($request,[
            "nomCommune"=>["required"],
            "serin"=> ["required","numeric", new Siren],
            "siret"=> ["numeric", "unique:communes", new Siret],
        ],[],[]);
        $client = Collectivite::create([
            "typeCollectivite"=>"Commune"
        ]);
        $commune = Commune::create($request->only(["nomCommune","serin", "siret"])+['id_collectivite'=>$client->id_collectivite,'date_enter'=>Carbon::now()]);
        return response([
            "ok"=>true,
            "data"=> $commune
        ],200);
    }

    public function update(Request $request){
        $this->validate($request,[
            "id_commune"=>["required","exists:communes"],
            "nomCommune"=>["required"],
            "serin"=> ["required","numeric", new Siren],
            "siret"=> ["numeric", Rule::unique('communes')->ignore($request["id_commune"], 'id_commune'), new Siret],
        ],[],[]);
        $commune = Commune::find($request["id_commune"]);
        $commune->update($request->only(["nomCommune", "serin", "siret"]));
        return response([
            "ok"=>true,
            "data"=>"Commune modifiée avec succée"
        ],200);
    }

    
}
