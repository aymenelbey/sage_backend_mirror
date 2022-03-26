<?php


namespace App\Http\Controllers;


use App\Models\InfoClientHistory;
use Illuminate\Http\Request;

class InfoHistoryController extends  Controller
{
    public  function fetchHistory(Request $request){
        $typeData=$request->get('type');
        $idData=$request->get('data');
        $typeColumn=$request->get('column');
        $list = InfoClientHistory::with('updated_by')->where('id_reference',$idData)
            ->where('referenced_table',$typeData)
            ->where('referenced_column',$typeColumn)
            ->orderBy('date_reference','DESC')
            ->get();
        return response([
            'ok' => true,
            'list' => $list
        ]);
    }
}
