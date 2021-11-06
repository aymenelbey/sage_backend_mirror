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
                $query=$query->withCount('sites');
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
                $query=$query->withCount('sites');
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
        $query=Departement::query()
        ->orderBy("slug_departement","ASC");
        $list=$query->paginate(15);
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
            ]
        );
        return response([
            'ok'=>true,
            'departement'=>$depart
        ]);
    }
    public function soft_delete(Request $request){
        $depart = Departement::find($request['idDep'])->delete();
        return response([
            'ok'=>'async',
            'departement'=>$request['idDep']
        ]);
    }
}