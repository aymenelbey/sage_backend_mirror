<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Syndicat extends Model
{
    use HasFactory,SoftDeletes;
    protected $primaryKey = "id_syndicat";
    protected $fillable = [
        "nomCourt",
        "denominationLegale",
        "serin",
        "adresse",
        'lat',
        'lang',
        "siteInternet",
        "telephoneStandard",
        "nombreHabitant",
        "logo",
        "ged_rapport",
        'amobe',
        'nature_juridique',
        'departement_siege',
        'competence_dechet',
        'region_siege',
        "email",
        "sinoe",
        "id_collectivite"
    ];
    protected $dates = ['deleted_at'];
    public function contacts(){
        return $this->belongsToMany(Contact::class, ContactHasPersonMoral::class,'idPersonMoral','id_contact','id_syndicat','id_contact')
        ->wherePivot('deleted_at', null)
        ->withPivot('function');
    }
    public function epics(){
        return $this->hasManyThrough(EPIC::class, SyndicatHasEpic::class,'id_syndicat','id_epic','id_syndicat','id_epic');
    }
    public function sites(){
        return $this->hasManyThrough(Site::class,ClientHasSite::class,'id_collectivite','id_site','id_collectivite','id_site');
    }
    public function logo(){
        return $this->hasMany(ImageSage::class,"uid","logo");
    }
    public function ged_rapport(){
        return $this->hasMany(ImageSage::class,"uid","ged_rapport");
    }
    public function withEnums(){
        $dep=$this->hasOne(Enemuration::class,'id_enemuration', 'departement_siege')->first();
        $reg=$this->hasOne(Enemuration::class, 'id_enemuration', 'region_siege')->first();
        $nat=$this->hasOne(Enemuration::class, 'id_enemuration', 'nature_juridique')->first();
        $amo=$this->hasOne(Enemuration::class, 'id_enemuration', 'amobe')->first();
        $com=$this->hasOne(Enemuration::class, 'id_enemuration', 'competence_dechet')->first();
        $this->departement_siege=$dep?$dep->__toString():'';
        $this->region_siege=$reg?$reg->__toString():'';
        $this->nature_juridique=$nat?$nat->__toString():'';
        $this->amobe=$amo?$amo->__toString():'';
        $this->competence_dechet=$com?$com->__toString():'';
    }

}