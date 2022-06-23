<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\DeleteChecks;

class Commune extends TrackableModel
{
    use HasFactory, SoftDeletes, DeleteChecks;
    protected $primaryKey = "id_commune";
    
    public $deleteChecks = ['contacts', 'files'];

    protected $fillable = [
        "nomCommune",
        "adresse",
        "insee",
        "logo",
        "serin",
        "siret",
        "departement_siege",
        "region_siege",
        'lat',
        'lang',
        "nombreHabitant",
        "date_enter",
        "city",
        "country",
        "postcode",
        'id_epic',
        'id_collectivite',
        'updated_by',
        'status',
        'status_updated_by'
    ];
    protected $dates = ['deleted_at'];
    protected $appends = ['typePersonMoral','dataIndex','id_person','name'];
    public function getTypePersonMoralAttribute(){
        return "Commune";
    }
    public function getIdPersonAttribute(){
        return $this->id_commune;
    }
    public function getDataIndexAttribute(){
        return "nomCommune";
    }
    public function getNameAttribute(){
        return "Nom Commune";
    }
    public function contacts(){
        return $this->belongsToMany(Contact::class, ContactHasPersonMoral::class,'idPersonMoral','id_contact','id_commune','id_contact')
        ->wherePivot('typePersonMoral', 'Commune')
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
        $dep=$this->departement_siege()->first();
        $reg=$this->region_siege()->first();
        $this->departement_siege=$dep?$dep->__toString():'';
        $this->region_siege=$reg?$reg->__toString():'';
    }
    public function logo(){
        return $this->hasMany(ImageSage::class,"uid","logo");
    }

    public function updated_by(){
        return $this->hasOne(Admin::class, 'id_admin', 'updated_by');
    }    
    public function effectif_history(){
        return InfoClientHistory::with('updated_by')->where('referenced_table', 'Commune')->where('id_reference', $this->id_commune)->orderBy('date_reference', 'DESC');
    }
    public function files(){
        return GEDFile::with('category')->where('type', 'communes')->where('entity_id', $this->id_commune);
    }
}