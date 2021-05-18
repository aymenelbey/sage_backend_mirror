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
        "GEDRapport",
        'amobe',
        'nature_juridique',
        'departement_siege',
        'competence_dechet',
        'region_siege',
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
    

}