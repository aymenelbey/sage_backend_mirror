<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Site;
use App\Models\Gestionnaire;
use JWTAuth;

class MapSitesController extends Controller
{
    public function getSites(Request $request){
        $lat=$request['lat'];$lang=$request['lang'];
        $catg=$request->get('catg');
        $mode=$request->get('mode');
        $reg=$request->get('reg');
        $dep=$request->get('dep');
        $function='where';
        $sitemapQuery = Site::query();
        if($catg && in_array($catg,['UVE',"TMB",'TRI','ISDND'])){
            $sitemapQuery=$sitemapQuery->{$function}("categorieSite","=",$catg);
        }
        if($mode && in_array($mode,['Gestion privée',"Prestation de service",'Regie','DSP'])){
            $sitemapQuery=$sitemapQuery->{$function}("modeGestion","=",$mode);
        }
        if($reg){
            $sitemapQuery=$sitemapQuery->whereHas('region_siege', function ($query)use($reg) {
                $query->where('name_region', 'ilike', "%$reg%")
                ->orWhere('slug_region', 'ilike', "%$reg%");
            });
        }
        if($dep){
            $sitemapQuery=$sitemapQuery->whereHas('departement_siege', function ($query)use($dep) {
                $query->where('name_departement', 'ilike', "%$dep%")
                ->orWhere('slug_departement', 'ilike', "%$dep%");
            });
        }
        if($lat){
            $lat=explode(',',$lat);
            $sitemapQuery=$sitemapQuery->whereBetween("latitude",$lat);
        }
        if($lang){
            $lang=explode(',',$lang);
            $sitemapQuery=$sitemapQuery->whereBetween("langititude",$lang);
        }
        $sitemapQuery=$sitemapQuery->skip(0)->take(50);
        $sites=$sitemapQuery->get(['latitude AS lat','langititude AS lang','id_site','adresse','categorieSite AS iconType']);
        return response([
            'ok'=>true,
            'data'=>$sites
        ]);
    }
    public function getSites_manager(Request $request){
        $gestionnaire = JWTAuth::user();
        $gestionnaire = Gestionnaire::where("id_user","=",$gestionnaire->id)->first();
        $lat=$request['lat'];$lang=$request['lang'];
        $sitemapQuery = Site::query();
        $catg=$request->get('catg');
        $mode=$request->get('mode');
        $reg=$request->get('reg');
        $dep=$request->get('dep');
        $function='where';
        $sitemapQuery=$sitemapQuery->whereHas('gestionnaire', function($query) use ($gestionnaire) {
            $query->where('gestionnaire_has_sites.id_gestionnaire', $gestionnaire->id_gestionnaire);
        });
        if($catg && in_array($catg,['UVE',"TMB",'TRI','ISDND'])){
            $sitemapQuery=$sitemapQuery->{$function}("categorieSite","=",$catg);
        }
        if($mode && in_array($mode,['Gestion privée',"Prestation de service",'Regie','DSP'])){
            $sitemapQuery=$sitemapQuery->{$function}("modeGestion","=",$mode);
        }
        if($reg){
            $sitemapQuery=$sitemapQuery->whereHas('region_siege', function ($query)use($reg) {
                $query->where('name_region', 'ilike', "%$reg%")
                ->orWhere('slug_region', 'ilike', "%$reg%");
            });
        }
        if($dep){
            $sitemapQuery=$sitemapQuery->whereHas('departement_siege', function ($query)use($dep) {
                $query->where('name_departement', 'ilike', "%$dep%")
                ->orWhere('slug_departement', 'ilike', "%$dep%");
            });
        }
        if($lat){
            $lat=explode(',',$lat);
            $sitemapQuery=$sitemapQuery->whereBetween("latitude",$lat);
        }
        if($lang){
            $lang=explode(',',$lang);
            $sitemapQuery=$sitemapQuery->whereBetween("langititude",$lang);
        }
        $sites=$sitemapQuery->get(['latitude AS lat','langititude AS lang','id_site','adresse','categorieSite AS iconType']);
        return response([
            'ok'=>true,
            'data'=>$sites
        ]);
    }
}