<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EPIC extends Model
{
    use HasFactory,SoftDeletes;
    protected $primaryKey = "id_epic";
    protected $table = "epics";
    protected $fillable = [
        "nomEpic",
        "serin",
        "adresse",
        'lat',
        'lang',
        'nom_court',
        'sinoe',
        "siteInternet",
        "telephoneStandard",
        "nombreHabitant",
        "logo",
        "nature_juridique",
        "departement_siege",
        "competence_dechet",
        "region_siege",
        "exerciceCompetance",
        "id_collectivite"
    ];

    protected $dates = ['deleted_at'];
    
    public function contacts(){
        return $this->belongsToMany(Contact::class, ContactHasPersonMoral::class,'idPersonMoral','id_contact','id_epic','id_contact')
        ->wherePivot('deleted_at', null);
    }
    public function communes(){
        return $this->hasMany(Commune::class,"id_epic");
    }
    public function syndicat(){
        return $this->hasOneThrough(Syndicat::class, SyndicatHasEpic::class,'id_epic','id_syndicat','id_epic','id_syndicat');
    }
    public function logo(){
        return $this->hasMany(ImageSage::class,"uid","logo");
    }
    public function nature_juridique(){
        return $this->hasOne(Enemuration::class,'id_enemuration', 'nature_juridique');
    }
    public function departement_siege(){
        return $this->hasOne(Enemuration::class,'id_enemuration', 'departement_siege');
    }
    public function region_siege(){
        return $this->hasOne(Enemuration::class,'id_enemuration', 'region_siege');
    }
    public function withEnums(){
        $dep=$this->hasOne(Enemuration::class,'id_enemuration', 'departement_siege')->first();
        $reg=$this->hasOne(Enemuration::class, 'id_enemuration', 'region_siege')->first();
        $nat=$this->hasOne(Enemuration::class, 'id_enemuration', 'nature_juridique')->first();
        $this->departement_siege=$dep?$dep->__toString():'';
        $this->region_siege=$reg?$reg->__toString():'';
        $this->nature_juridique=$nat?$nat->__toString():'';
    }
}