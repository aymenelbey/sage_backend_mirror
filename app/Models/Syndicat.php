<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\DeleteChecks;

class Syndicat extends TrackableModel
{
    use HasFactory, DeleteChecks;
    protected $primaryKey = "id_syndicat";
    
    public $deleteChecks = ['contacts', 'sites', 'files', 'epics', 'competance_exercee', 'competance_delegue', 'competance_recu'];
        
    protected $fillable = [
        "nomCourt",
        "denominationLegale",
        "serin",
        "siret",
        "adresse",
        'lat',
        'lang',
        "siteInternet",
        "telephoneStandard",
        "nombreHabitant",
        "date_enter",
        "logo",
        "ged_rapport",
        'amobe',
        'nature_juridique',
        'departement_siege',
        'region_siege',
        "email",
        "sinoe",
        "city",
        "country",
        "postcode",
        "id_collectivite",
        'status',
        'updated_by',
        'status_updated_by'
    ];
    // protected $dates = ['deleted_at'];
    protected $appends = ['typePersonMoral','dataIndex','id_person','name'];
    public function getTypePersonMoralAttribute(){
        return "Syndicat";
    }
    public function getIdPersonAttribute(){
        return $this->id_syndicat;
    }
    public function getDataIndexAttribute(){
        return "nomCourt";
    }
    public function getNameAttribute(){
        return "Nom Court";
    }
    public function contacts(){
        return $this->belongsToMany(Contact::class, ContactHasPersonMoral::class,'idPersonMoral','id_contact','id_syndicat','id_contact')
        ->wherePivot('typePersonMoral', 'Syndicat')
        ->wherePivot('deleted_at', null);
    }
    public function sites(){
        return $this->hasManyThrough(Site::class,ClientHasSite::class,'id_collectivite','id_site','id_collectivite','id_site');
    }
    public function logo(){
        return $this->hasMany(ImageSage::class,"uid","logo");
    }
    public function epics(){
        return $this->hasMany(EPIC::class,"id_syndicat","id_syndicat");
    }
    public function ged_rapport(){
        return $this->hasMany(ImageSage::class,"uid","ged_rapport");
    }
    public function nature_juridique(){
        return $this->hasOne(Enemuration::class,'id_enemuration', 'nature_juridique');
    }
    public function departement_siege(){
        return $this->hasOne(Departement::class,'id_departement', 'departement_siege');
    }
    public function region_siege(){
        return $this->hasOne(Region::class,'id_region', 'region_siege');
    }
    public function amobe(){
        return $this->hasOne(Enemuration::class,'id_enemuration', 'amobe');
    }
    /* competances */
    public function competance_exercee(){
        return $this->hasMany(CompetanceDechet::class,'owner_competance', 'id_syndicat')
        ->where('owner_type','Syndicat')
        ->whereNull('delegue_competance');
    }
    public function competance_delegue(){
        return $this->hasMany(CompetanceDechet::class,'owner_competance', 'id_syndicat')
        ->with('delegue_competance')
        ->where('owner_type','Syndicat')
        ->whereNotNull('delegue_competance');
    }
    public function competance_recu(){
        return $this->hasMany(CompetanceDechet::class,'delegue_competance', 'id_syndicat')
        ->with('owner_competance')
        ->where('delegue_type','Syndicat');
        //->with('owner_competance');
    }
    /* end competances */
    public function withEnums(){
        $dep=$this->hasOne(Departement::class,'id_departement', 'departement_siege')->first();
        $reg=$this->hasOne(Region::class,'id_region', 'region_siege')->first();
        $nat=$this->hasOne(Enemuration::class, 'id_enemuration', 'nature_juridique')->first();
        $amo=$this->hasOne(Enemuration::class, 'id_enemuration', 'amobe')->first();
        $this->departement_siege=$dep?$dep->__toString():'';
        $this->region_siege=$reg?$reg->__toString():'';
        $this->nature_juridique=$nat?$nat->__toString():'';
        $this->amobe=$amo?$amo->__toString():'';
    }
    public function updated_by(){
        return $this->hasOne(Admin::class, 'id_admin', 'updated_by');
    }
    public function status_updated_by(){
        return $this->hasOne(Admin::class,'id_admin', 'status_updated_by');
    }
    public function effectif_history(){
        return InfoClientHistory::with('updated_by')->where('referenced_table', 'Syndicat')->where('id_reference', $this->id_syndicat)->orderBy('date_reference', 'DESC');
    }
    public function files(){
        return GEDFile::with('category')->where('type', 'syndicats')->where('entity_id', $this->id_syndicat);
    }

}