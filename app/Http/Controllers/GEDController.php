<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GEDFile;
use App\Models\Enemuration;
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
            'fileCategory' => 'required',
            'shareable' => 'required',
            'date' => 'required'
        ]);

        if($request->file('file')) {
            $entities = json_decode($request->input('entities'), 1);
            if($entities && sizeof($entities) > 0){
                $entity = $entities[0];

                $fileCategory = Enemuration::find($request->input('fileCategory'));


                $file_to_add = [
                    'date' => $request->input('date'),
                    'category' => $request->input('fileCategory'),
                    'type' => $entity['type'],
                    'shareable' => $request->input('shareable'),
                ];

                switch($entity['type']){
                    case 'epics':
                        $file_to_add['entity_id'] = $entity['elem']['id_epic'];
                        $file_to_add['name'] = $entity['elem']['nomEpic'];
                        break;
                    case 'syndicats':
                        $file_to_add['entity_id'] = $entity['elem']['id_syndicat'];
                        $file_to_add['name'] = $entity['elem']['nomCourt'];
                        break;
                    case 'communes':
                        $file_to_add['entity_id'] = $entity['elem']['id_commune'];
                        $file_to_add['name'] = $entity['elem']['nomCommune'];
                        break;
                    case 'societies':
                        $file_to_add['entity_id'] = $entity['elem']['id_societe_exploitant'];
                        $file_to_add['name'] = $entity['elem']['denomination'];
                        break;
                    case 'sites':
                        $file_to_add['entity_id'] = $entity['elem']['id_site'];
                        $file_to_add['name'] = $entity['elem']['denomination'];
                        break;
                }

                $file_to_add['name'] = str_replace(' ', '', $file_to_add['name']);
                $file_to_add['name'] .= '_'.$fileCategory->code.'_'.$file_to_add['date'].'_'.time();
                $file_to_add['name'] .= '.'.$request->file('file')->extension();
                $filePath = $request->file('file')->storeAs('GED', $file_to_add['name'], 'public');

                if(!$filePath){
                    return response([
                        "ok" => false,
                        "message"=>"Fichier non crée"
                    ], 200);
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
