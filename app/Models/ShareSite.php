<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShareSite extends Model
{
    use HasFactory,SoftDeletes;
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
    public function transform_columns(){
        $finalRes=[];
        $clmns=explode("&",$this->columns);
        foreach($clmns as $clm){
            $tmp=explode('$',$clm);
            if(count($tmp)==2){
                $finalRes[$tmp[0]]=$tmp[1];
            }
        }
        $this->columns=$finalRes;
    }
    protected static function booted()
    {
        static::retrieved(function ($model) {
            if($model->type_data_share==="Departement" || $model->type_data_share==="Region"){
                $finalRes=[];
                $clmns=explode("&",$model->columns);
                foreach($clmns as $clm){
                    $tmp=explode('$',$clm);
                    if(count($tmp)==2){
                        $finalRes[$tmp[0]]=$tmp[1];
                    }
                }
                $model->columns=$finalRes;
            }
        });
    }
}