<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Region;

class RegionController extends Controller
{
    public function index(Request $request){
        $search=$request->get("search");
        $all=$request->get("all");
        $with_count=$request->get('hasCount');
        if($search){
            $query=Region::query();
            $query=$query->where("name_region","ILIKE","%{$search}%")
            ->orWhere("slug_region","ILIKE","%{$search}%")
            ->skip(0)->take(10)
            ->select("id_region AS value","name_region AS label");
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
        } if($all){
            $query=Region::query();
            $query=$query->select("id_region AS value","name_region AS label");
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
        $query=Region::query()->with('departements')->orderBy("region_code","ASC");
        $list=$query->paginate(100);
        return response([
            'message'=>'async',
            'list'=>$list
        ]);
    }
    public function create(Request $request){
        $region = Region::updateOrCreate(
            ['id_region' =>$request->id_region],
            [
                'region_code' => $request->region_code,
                'name_region' => $request->name_region,
                'slug_region' => $request->slug_region,
            ]
        );
        return response([
            'ok'=>true,
            'region'=>$region
        ]);
    }
    public function soft_delete(Request $request){
        try{
            $depart = Region::find($request['idReg'])->delete();
            return response([
                'ok'=>'async',
                'region'=>$request['idReg']
            ]);
        }catch(\Exception $e){
            return response([
                "errors"=> true,
                'message'=> 'Item already in use'
            ]);
        }
    }
}