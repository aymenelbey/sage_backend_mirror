<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Region;

class RegionController extends Controller
{
    public function index(Request $request){
        $search=$request->get("search");
        $with_count=$request->get('hasCount');
        if($search){
            $query=Region::query();
            $query=$query->where("name_region","ILIKE","%{$search}%")
            ->orWhere("slug_region","ILIKE","%{$search}%")
            ->skip(0)->take(10)
            ->select("id_region AS value","name_region AS label");
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