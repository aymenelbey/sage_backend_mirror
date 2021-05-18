<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\DataTechn;
use Illuminate\Database\Eloquent\SoftDeletes;

class Site extends Model
{
    use HasFactory,SoftDeletes;
    protected $primaryKey = "id_site";
    protected $fillable = [
        "denomination",
        "categorieSite",
        "adresse",
        "latitude",
        "langititude",
        "siteIntrnet",
        "telephoneStandrad",
        "anneeCreation",
        "photoSite",
        "modeGestion",
        "perdiocitRelance"
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
}