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
        $function='where';
        $sitemapQuery = Site::query();
        if($catg && in_array($catg,['UVE',"TMB",'TRI','ISDND'])){
            $sitemapQuery=$sitemapQuery->{$function}("categorieSite","=",$catg);
            $function='orWhere';
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
            'data'=>$sites,
            "zoom"=>$lat
        ]);
    }
    public function getSites_manager(Request $request){
        $gestionnaire = JWTAuth::user();
        $gestionnaire = Gestionnaire::where("id_user","=",$gestionnaire->id)->first();
        $lat=$request['lat'];$lang=$request['lang'];
        $range=$this->getRange($zoom);
        $catg=$request->get('catg');
        $function='where';
        $sitemapQuery = Site::query();
        $sitemapQuery=$sitemapQuery->whereHas('gestionnaire', function($query) use ($gestionnaire) {
            $query->where('gestionnaire_has_sites.id_gestionnaire', $gestionnaire->id_gestionnaire);
        });
        if($catg && in_array($catg,['UVE',"TMB",'TRI','ISDND'])){
            $sitemapQuery=$sitemapQuery->{$function}("categorieSite","=",$catg);
            $function='orWhere';
        }
        $sites=$sitemapQuery->get(['latitude AS lat','langititude AS lang','id_site','adresse','categorieSite AS iconType']);
        return response([
            'ok'=>true,
            'data'=>$sites
        ]);
    }
    protected function getRange($zoom){
        if($zoom>5){
            $zoomRange=5;
            $startCount=4;
            while($startCount<$zoom){
                $zoomRange=(($startCount+1)*$zoomRange)/($startCount*2);
                $startCount++;
            }
            return $zoomRange;
        }
        return 5;
    }
}