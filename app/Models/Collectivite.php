<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Collectivite extends Model
{
    use HasFactory,SoftDeletes;
    protected $primaryKey = "id_collectivite";
    protected $fillable = [
        'typeCollectivite',
        "id_user_premieum"
    ];
    protected $dates = ['deleted_at'];
    public function client(){
        return $this->morphTo(__FUNCTION__,'typeCollectivite','id_collectivite',"id_collectivite");
    }
}