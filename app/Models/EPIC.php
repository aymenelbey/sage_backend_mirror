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
        ->wherePivot('deleted_at', null)
        ->withPivot('function');
    }
    public function communes(){
        return $this->hasMany(Commune::class,"id_epic");
    }
    public function syndicat(){
        return $this->hasOneThrough(Syndicat::class, SyndicatHasEpic::class,'id_epic','id_syndicat','id_epic','id_syndicat');
    }
}