<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\EPIC;
use App\Models\Syndicat;
use App\Models\Commune;
use App\Models\Site;
use App\Models\Departement;

class Region extends Model
{
    use HasFactory,SoftDeletes;
    protected $primaryKey = "id_region";
    
    protected $fillable = [
        "region_code",
        "name_region",
        "slug_region"
    ];

    protected $dates = ['deleted_at'];

    public function __toString(){
        return $this->name_region;
    }
    
    public function sites(){
        return $this->hasMany(Site::class,"region_siege","id_region");
    }

    public function departements(){
        return $this->hasMany(Departement::class,"region_code","region_code");
    }

    public function delete(){
        if(EPIC::where('region_siege', $this->id_region)->exists() || Commune::where('region_siege', $this->id_region)->exists() || Syndicat::where('region_siege', $this->id_region)->exists() || Site::where('region_siege', $this->id_region)->exists() || Departement::where('region_code', $this->region_code)->exists()){
            throw new \Exception('cantdelete');
        }
        return $this->delete(); 
    }

}