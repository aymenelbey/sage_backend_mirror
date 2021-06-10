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
        "region_siege",
        "id_collectivite"
    ];

    protected $dates = ['deleted_at'];
    protected $appends = ['typePersonMoral','dataIndex','id_person'];
    public function getTypePersonMoralAttribute(){
        return "EPIC";
    }
    public function getIdPersonAttribute(){
        return $this->id_epic;
    }
    public function getDataIndexAttribute(){
        return "nomEpic";
    }
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
        return $this->hasOne(Departement::class,'id_departement', 'departement_siege');
    }
    /* competances */
    public function competance_exercee(){
        return $this->hasMany(CompetanceDechet::class,'owner_competance', 'id_epic')->where('owner_type','EPIC')->whereNull('delegue_competance');
    }
    public function competance_delegue(){
        return $this->hasMany(CompetanceDechet::class,'owner_competance', 'id_epic')->with('delegue_competance')->where('owner_type','EPIC')->whereNotNull('delegue_competance');
    }
    public function competance_recu(){
        return $this->hasMany(CompetanceDechet::class,'delegue_competance', 'id_epic')->where('delegue_type','EPIC')->with('owner_competance');
    }
    /* end competances */
    public function region_siege(){
        return $this->hasOne(Region::class,'id_region', 'region_siege');
    }
    public function withEnums(){
        $dep=$this->hasOne(Departement::class,'id_departement', 'departement_siege')->first();
        $reg=$this->hasOne(Region::class,'id_region', 'region_siege')->first();
        $nat=$this->hasOne(Enemuration::class, 'id_enemuration', 'nature_juridique')->first();
        $this->departement_siege=$dep?$dep->__toString():'';
        $this->region_siege=$reg?$reg->__toString():'';
        $this->nature_juridique=$nat?$nat->__toString():'';
    }
}