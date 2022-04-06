<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


use App\Models\EPIC;
use App\Models\Syndicat;
use App\Models\Commune;
use App\Models\Site;

class Departement extends Model
{
    use HasFactory,SoftDeletes;
    protected $primaryKey = "id_departement";
    protected $fillable = [
        "region_code",
        "departement_code",
        "name_departement",
        "slug_departement",
        "region_code"
    ];
    protected $dates = ['deleted_at'];
    public function __toString()
    {
        return $this->name_departement;
    }
    public function sites(){
        return $this->hasMany(Site::class,"departement_siege","id_departement");
    }

    public function region(){
        return $this->belongsTo(Region::class, "region_code", "region_code");
    }

    public function delete(){
        if(EPIC::where('departement_siege', $this->id_departement)->exists() || Commune::where('departement_siege', $this->id_departement)->exists() || Syndicat::where('departement_siege', $this->id_departement)->exists() || Site::where('departement_siege', $this->id_departement)->exists()){
            throw new \Exception('cantdelete');
        }
        return $this->delete(); 
    }
}