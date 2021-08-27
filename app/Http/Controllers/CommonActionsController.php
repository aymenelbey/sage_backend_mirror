<?php

namespace App\Http\Controllers;

use App\Models\ImageSage;
use Illuminate\Http\Request;
use App\Models\Region;
use App\Models\Departement;
use JWTAuth;
use DB;

class CommonActionsController extends Controller{
    public function move_file(Request $request){
        if($request['file'])
         {
            $path=$request->file('file')->store('images');
            $image=ImageSage::create([
                "name"=>$request->file('file')->getClientOriginalName(),
                "status"=>"done",
                "url"=>$path
            ]);
            return response([
                'ok'=>true,
                'image'=>[
                    "name"=>$image->name,
                    "url"=>asset($image->url),
                    "status"=>$image->status,
                    "uid"=>$image->uid
                ]
            ],200);
         }
    }
    public function localization(Request $request){
        $user = JWTAuth::user()->userType;
        $regions=Region::whereExists( function ($query) use ($user) {
            $query->select(DB::raw(1))
            ->from('share_sites')
            ->whereRaw('regions.id_region = share_sites.id_data_share')
            ->where('share_sites.id_user_premieum', '=', $user->id_user_premieum)
            ->where('share_sites.type_data_share', '=', "Region");
        })->get(['id_region as index','name_region as value']);
        $departments=Departement::whereExists( function ($query) use ($user) {
            $query->select(DB::raw(1))
            ->from('share_sites')
            ->whereRaw('departements.id_departement = share_sites.id_data_share')
            ->where('share_sites.id_user_premieum', '=', $user->id_user_premieum)
            ->where('share_sites.type_data_share', '=', "Departement");
        })->get(['id_departement as index','name_departement as value']);
        return response([
            "ok"=>true,
            "regions"=>$regions,
            "departments"=>$departments
        ],200);
    }
}