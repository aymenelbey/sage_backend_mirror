<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Commune extends Model
{
    use HasFactory,SoftDeletes;
    protected $primaryKey = "id_commune";
    protected $fillable = [
        "nomCommune",
        "adresse",
        "insee",
        "logo",
        "serin",
        "departement_siege",
        "region_siege",
        'lat',
        'lang',
        "nombreHabitant",
        'id_epic',
        'id_collectivite',
    ];
    protected $dates = ['deleted_at'];
    public function contacts(){
        return $this->belongsToMany(Contact::class, ContactHasPersonMoral::class,'idPersonMoral','id_contact','id_commune','id_contact')
        ->wherePivot('deleted_at', null);
    }
    public function epic(){
        return $this->belongsTo(EPIC::class,"id_epic");
    }
     public function departement_siege(){
        return $this->hasOne(Departement::class,'id_departement', 'departement_siege');
    }
    public function region_siege(){
        return $this->hasOne(Region::class,'id_region', 'region_siege');
    }
    public function withEnums(){
        $dep=$this->hasOne(Enemuration::class,'id_enemuration', 'departement_siege')->first();
        $reg=$this->hasOne(Enemuration::class, 'id_enemuration', 'region_siege')->first();
        $this->departement_siege=$dep?$dep->__toString():'';
        $this->region_siege=$reg?$reg->__toString():'';
    }
    public function logo(){
        return $this->hasMany(ImageSage::class,"uid","logo");
    }
    
}