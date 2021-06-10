<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Departement;

class DepartementController extends Controller
{
    public function index(Request $request){
        $search=$request->get("search");
        $with_count=$request->get('hasCount');
        if($search){
            $query=Departement::query();
            $query=$query->where("name_departement","ILIKE","%{$search}%")
            ->orWhere("slug_departement","ILIKE","%{$search}%")
            ->skip(0)->take(10)
            ->select("id_departement AS value","name_departement AS label");
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
}