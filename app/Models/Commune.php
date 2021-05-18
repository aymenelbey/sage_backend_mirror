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
        'lat',
        'lang',
        "nombreHabitant",
        'id_epic',
        'id_collectivite',
    ];
    protected $dates = ['deleted_at'];
    
    public function contacts(){
        return $this->belongsToMany(Contact::class, ContactHasPersonMoral::class,'idPersonMoral','id_contact','id_commune','id_contact')
        ->wherePivot('deleted_at', null)
        ->withPivot('function');
    }
    public function epic(){
        return $this->belongsTo(EPIC::class,"id_epic");
    }
}