<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Departement;

class DepartementController extends Controller
{
    public function index(Request $request){
        $search=$request->get("search");
        $all=$request->get("all");
        $with_count=$request->get('hasCount');
        if($search){
            $query=Departement::query();
            $query=$query->where("name_departement","ILIKE","%{$search}%")
            ->orWhere("slug_departement","ILIKE","%{$search}%")
            ->select("id_departement AS value","name_departement AS label");
            if($with_count){
                if($request->has("category") && !empty($request->get("category")) && $request->get("category") != 'all'){
                    $query=$query->withCount(['sites' => function ($query) use ($request){
                        return $query->where('sites.categorieSite', $request->get("category"));
                    }]);
                }else{
                    $query=$query->withCount('sites');
                }
            }
            $list=$query->get();
            return response([
                'message'=>'async',
                'list'=>$list
            ]);
        }else if($all){
            $query=Departement::query();
            $query=$query->select("id_departement AS value","name_departement AS label");
            if($with_count){
                if($request->has("category") && !empty($request->get("category")) && $request->get("category") != 'all'){
                    $query=$query->withCount(['sites' => function ($query) use ($request){
                        return $query->where('sites.categorieSite', $request->get("category"));
                    }]);
                }else{
                    $query=$query->withCount('sites');
                }
            }
            $list=$query->get();
            return response([
                'message'=>'async',
                'list'=>$list
            ]);
        }
        return response([
            'message'=>'close',
            'list'=>[]
        ]);
    }
    public function fetch_list(Request $request){
        $query=Departement::query()->with('region')->orderBy("departement_code","ASC");
        $list=$query->paginate(120);
        return response([
            'message'=>'async',
            'list'=>$list
        ]);
    }
    public function create(Request $request){
        $depart = Departement::updateOrCreate(
            ['id_departement' =>$request->id_departement],
            [
                'departement_code' => $request->departement_code,
                'name_departement' => $request->name_departement,
                'slug_departement' => $request->slug_departement,
                'region_code' => $request->region_code,
            ]
        );
        $depart = Departement::with('region')->find($request->id_departement);
        return response([
            'ok'=>true,
            'departement'=>$depart
        ]);
    }
    public function soft_delete(Request $request){
        try{
            $depart = Departement::find($request['idDep'])->delete();
            return response([
                'ok'=>'async',
                'departement'=>$request['idDep']
            ]);
        }catch(\Exception $e){
            return response([
                "errors"=> true,
                'message'=> 'Item already in use'
            ]);
        }
    }
}