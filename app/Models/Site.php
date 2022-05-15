<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\DataTechn;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;


class Site extends TrackableModel {
    use HasFactory, SoftDeletes;
    protected $primaryKey = "id_site";

    protected $fillable = [
        "denomination",
        "categorieSite",
        "departement_siege",
        "region_siege",
        "sinoe",
        "adresse",
        "latitude",
        "langititude",
        "siteIntrnet",
        "telephoneStandrad",
        "anneeCreation",
        "photoSite",
        "modeGestion",
        "perdiocitRelance",
        'status',
        'updated_by',
        'status_updated_by'
    ];

    protected $dates = ['deleted_at'];
    public function dataTech(){
        return $this->hasOne(DataTechn::class,"id_site","id_site");
    }
    public function contracts(){
        return $this->hasMany(Contrat::class,"id_site");
    }
    public function photos(){
        return $this->hasMany(ImageSage::class,"ref_id","id_site");
    }
    public function gestionnaire(){
        return $this->hasOneThrough(Gestionnaire::class,GestionnaireHasSite::class,"id_site","id_gestionnaire","id_site","id_gestionnaire");
    }
    public function client(){
        return $this->hasOneThrough(Collectivite::class,ClientHasSite::class,"id_site","id_collectivite","id_site","id_collectivite");
    }
    public function exploitant(){
        return $this->hasOne(SocieteExpSite::class,"id_site");
    }
    public function departement_siege(){
        return $this->hasOne(Departement::class,'id_departement', 'departement_siege');
    }
    public function region_siege(){
        return $this->hasOne(Region::class,'id_region', 'region_siege');
    }
    public function updated_by(){
        return $this->hasOne(Admin::class, 'id_admin', 'updated_by');
    }
    public function status_updated_by(){
        return $this->hasOne(Admin::class,'id_admin', 'status_updated_by');
    }
    public function files($category = null){
        if($category && !empty($category)){
            return GEDFile::with(['category'])->whereHas('category', function ($query) use ($category){
                return $query->whereIn('code', array_values($category));
            })->where('type', 'sites')->where('entity_id', $this->id_site);

        }
        return GEDFile::with('category')->where('type', 'sites')->where('entity_id', $this->id_site);
    }
}