<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Region;

class RegionController extends Controller
{
    public function index(Request $request){
        $search=$request->get("search");
        if($search){
            $list=Region::where("name_region","ILIKE","%{$search}%")
            ->orWhere("slug_region","ILIKE","%{$search}%")
            ->skip(0)->take(10)
            ->get(["id_region AS value","name_region AS label"]);
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