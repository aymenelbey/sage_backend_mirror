<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
    public function __toString()
    {
        return $this->name_region;
    }
    public function sites(){
        return $this->hasMany(Site::class,"region_siege","id_region");
    }
}