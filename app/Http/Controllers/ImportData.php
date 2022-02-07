<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImportData extends Controller
{
    const JOBS_PROCESS=[
        'Syndicats'=>'\App\Jobs\ImportSyndicats',
        'Regions'=>'\App\Jobs\ImportRegions',
        'Departments'=>'\App\Jobs\ImportDepartments',
        'Epics'=>'\App\Jobs\ImportEpics',
        'Communes'=>'\App\Jobs\ImportCommunes',
        'Companies'=>'\App\Jobs\ImportCompanies',
        'Compositions'=>'\App\Jobs\ImportCompositionSyndicat',
        'Gestionaires'=>'\App\Jobs\ImportGestionaires',
        'SitesTMB'=>'\App\Jobs\ImportSitesTMB',
        'SitesISDND'=>'\App\Jobs\ImportSitesISDND',
        'SitesUVE'=>'\App\Jobs\ImportSitesUVE',
        'ContractUVEExploitant'=>'\App\Jobs\ImportContractUVEExploitant',
        'Contacts'=>'\App\Jobs\ImportContacts',
    ];
    public function import(Request $request)
    {
        $this->validate($request,[
            'file'=>['required','file','mimes:xlsx'],
            'typeData'=>['required','in:Syndicats,Regions,Departments,Epics,Communes,Companies,Compositions,Gestionaires,SitesTMB,SitesISDND,SitesUVE,ContractUVEExploitant,Contacts']
        ]);
        $user=auth()->user();
        $path = $request->file('file')->store("imports/$request->typeData/$user->id");
        (self::JOBS_PROCESS[$request->typeData])::dispatch($path,$user);
        return response([
            'message'=>"Import Started",
            'type' => $request->typeData,
            "path" => $path,
            "job" => self::JOBS_PROCESS[$request->typeData],
            'ok'=>true
        ]);
    }
    public function download_excel(Request $request)
    {
        $fileName=str_replace('_','/',$request['filename']).".xlsx";
        return response()->download(storage_path('app/'.$fileName));
    }
    
}