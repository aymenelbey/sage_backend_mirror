<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Departement extends Model
{
    use HasFactory,SoftDeletes;
    protected $primaryKey = "id_departement";
    protected $fillable = [
        "region_code",
        "departement_code",
        "name_departement",
        "slug_departement"
    ];
    protected $dates = ['deleted_at'];
    public function __toString()
    {
        return $this->name_departement;
    }
    public function sites(){
        return $this->hasMany(Site::class,"departement_siege","id_departement");
    }
}