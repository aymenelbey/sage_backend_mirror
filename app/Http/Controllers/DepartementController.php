<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Departement;

class DepartementController extends Controller
{
    public function index(Request $request){
        $search=$request->get("search");
        if($search){
            $list=Departement::where("name_departement","ILIKE","%{$search}%")
            ->orWhere("slug_departement","ILIKE","%{$search}%")
            ->skip(0)->take(10)
            ->get(["id_departement AS value","name_departement AS label"]);
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