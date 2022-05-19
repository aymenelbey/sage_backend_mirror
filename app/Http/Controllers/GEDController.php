<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GEDFile;
use App\Models\GEDFileEntity;

class GEDController extends Controller
{
    public function show(Request $request){
        $per_page = 10;
        if($request->has('per_page')){
            $per_page = $request->input('per_page');
        }
        $files = GEDFile::with(['category'])->paginate($per_page);
        
        foreach($files as $file){
            $file->entity = $file->entity();
            $file->path = $file->getPath();
        }

        return response([
            "ok"=>"server",
            "data"=> $files
        ], 200);
    }
    public function getGEDFile(Request $request, $file_id){
        // $this->validate($request, [
        //     'file_id' => 'required',
        // ]);
        if($file_id){
            $file = GEDFile::with(['category'])->find($file_id);
            
            if(!$file) {
                return response([
                    "ok"=> false,
                    "data"=> ''
                ], 500);
            }

            $file->entity = $file->entity();
            $file->path = $file->getPath();
            
            return response([
                "ok"=> true,
                "data"=> $file
            ], 200);
        }else{
            return response([
                "ok"=> false,
                "data"=> false
            ], 200);
        }
        
    }

    public function destroy(Request $request)
    {
        
        if(isset($request['files']) && is_array($request['files'])){
            $deletedLis=[];
            
            foreach($request['files'] as $file){
                $file = GEDFile::find($file);
                if($file){
                    $deletedLis [] = $file;
                    $file->delete();
                }
            }

            return response([
                'ok'=> true,
                'data'=> "async",
                'files'=> $deletedLis
            ]);
        }

        return response([
            'ok'=>false,
            'data'=>"no action"
        ],400);
    }

    public function create(Request $request){
        $this->validate($request, [
            'file' => 'required|mimes:pdf,jpeg,jpg,png',
            'entities' => 'required',
            'fileCategory' => 'required'
        ]);

        if($request->file('file')) {
            $name = time().'_'.$request->file->getClientOriginalName();
            $filePath = $request->file('file')->storeAs('GED', $name, 'public');
            $entities = json_decode($request->input('entities'), 1);
            if($entities && sizeof($entities) > 0){
                $entity = $entities[0];

                $file_to_add = [
                    'name' => $name,
                    'date' => $request->input('date'),
                    'category' => $request->input('fileCategory'),
                    'type' => $entity['type'],
                ];

                switch($entity['type']){
                    case 'epics':
                        $file_to_add['entity_id'] = $entity['elem']['id_epic'];
                        break;
                    case 'syndicats':
                        $file_to_add['entity_id'] = $entity['elem']['id_syndicat'];
                        break;
                    case 'communes':
                        $file_to_add['entity_id'] = $entity['elem']['id_commune'];
                        break;
                    case 'societies':
                        $file_to_add['entity_id'] = $entity['elem']['id_societe_exploitant'];
                        break;
                    case 'sites':
                        $file_to_add['entity_id'] = $entity['elem']['id_site'];
                        break;
                }

                $ged_file = GEDFile::create($file_to_add);
                if($ged_file){
                    return response([
                        "ok"=>true,
                        "data" => $ged_file,
                        "message"=>"Fichier crée avec succées"
                    ],200);
                }else{
                    return response([
                        "ok" => false,
                        "message"=>"Fichier non crée"
                    ],200);
                }   
            }
        }
    }
}
