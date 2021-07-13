<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class ShareSite extends Model
{
    use HasFactory,SoftDeletes;
    CONST VALID_COLOMNS=["denomination","categorieSite","adresse","siteIntrnet","telephoneStandrad","anneeCreation","photoSite","modeGestion","perdiocitRelance","capaciteRestante","capaciteReglementaire","projetExtension","dateExtension","dateOuverture","dateFermeture","dateFermeturePrev","quantiteRefus","CSRProduit","envoiPreparation","tonnageAnnuel","capaciteNominal","dernierConstruct","typeInstallation","typeDechetAccepter","technologie","valorisationEnergitique","autreActivite", "capaciteHoraire","capaciteNominale","dernierConstructeur","extension",'nombreFours',"capacite","nombreChaudiere","debitEau","miseEnService","typeFoursChaudiere","capaciteMaxAnu","videFour","reseauChaleur","rsCommentaire","tonnageReglementaireAp","performenceEnergetique","cycleVapeur","terboalternateur","venteProduction","typeDechetRecus","traitementFumee","installationComplementair","voiTraiFemuee","traitementNOX","equipeProcessTF","reactif","typeTerboalternateur","constructeurInstallation"];
    protected $primaryKey = "id_share_site";
    protected $fillable = [
        'start',
        'end',
        'columns',
        'id_user_premieum',
        'id_data_share',
        'type_data_share',
        'id_admin',
        'is_blocked'
    ];
    protected $dates = ['deleted_at'];
    public function site(){
        return $this->hasOne(Site::class,"id_site","id_data_share");
    }
    public function departement(){
        return $this->hasOne(Departement::class,"id_departement","id_data_share");
    }
    public function region(){
        return $this->hasOne(Region::class,"id_region","id_data_share");
    }
    protected static function booted()
    {
        static::retrieved(function ($model) {
            $finalRes=[];
            if($model->type_data_share==="Departement" || $model->type_data_share==="Region"){
                $clmns=explode("&",$model->columns);
                foreach($clmns as $clm){
                    $tmp=explode('$',$clm);
                    if(count($tmp)==2){
                        $finalRes[$tmp[0]]=[];
                        $toRetreive=explode('|',$tmp[1]);
                        foreach($toRetreive as $retr){
                            $finalRes[$tmp[0]][$retr]=true; 
                        }
                    }
                } 
            }else{
                $clmns=explode("|",$model->columns);
                foreach($clmns as $clmn){
                    $finalRes[$clmn]=true; 
                }
            }
            $model->columns=$finalRes;
        });
         static::saving(function ($model) {
            $columns='';
            if($model->type_data_share==="Site"){
                foreach($model->columns as $key=>$value){
                    if(in_array($key,self::VALID_COLOMNS) && $value){
                        $columns.=$key.'|';
                    }
                }
                $columns=substr($columns, 0, -1);
            }else{
                foreach($model->columns as $key=>$value){
                    if($value){
                        $typeSites []=$key;
                        $columns.=$key.'$';
                        foreach($value as $key2=>$value2){
                            if(in_array($key2,self::VALID_COLOMNS) && $value2){
                                $columns.=$key2.'|';
                            }
                        }
                        $columns=substr($columns, 0, -1);
                        $columns.='&';
                    }   
                }
                $columns=substr($columns, 0, -1);
            }
            $model->columns=$columns;
        });
        static::saved(function ($model) {
            $typeSites=[];
            if($model->type_data_share!="Site"){
                TypeSharedSite::where("id_share_site",$model->id_share_site)->delete();
                $clmns=explode("&",$model->columns);
                foreach($clmns as $clm){
                    $tmp=explode('$',$clm);
                    if(count($tmp)==2 && $tmp[0]!="generalInfo"){
                        $typeSites []=[
                            "site_categorie"=>$tmp[0],
                            "id_share_site"=>$model->id_share_site,
                            "created_at"=>Carbon::now(),
                            "updated_at"=>Carbon::now()
                        ];
                    }
                }
                TypeSharedSite::insert($typeSites);
            }
        });
    }
}