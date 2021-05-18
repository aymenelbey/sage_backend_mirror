<?php

namespace App\Http\Controllers;

use App\Models\ImageSage;
use Illuminate\Http\Request;

class CommonActionsController extends Controller{
    public function move_file(Request $request){
        if($request['file'])
         {
            $path=$request->file('file')->store('images');
            $path=asset($path);
            $image=ImageSage::create([
                "name"=>$request->file('file')->getClientOriginalName(),
                "status"=>"done",
                "url"=>$path
            ]);
            return response([
                'ok'=>true,
                'image'=>[
                    "name"=>$image->name,
                    "url"=>$image->url,
                    "status"=>$image->status,
                    "uid"=>$image->uid
                ]
            ],200);
         }
    }
}