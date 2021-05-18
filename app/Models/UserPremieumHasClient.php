<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserPremieumHasClient extends Model
{
    use HasFactory,SoftDeletes;
    protected $primaryKey = "id_user_has_client";
    protected $fillable = [
        "typeClient",
        "id_client",
        "id_user_premieum"
    ];
    protected $dates = ['deleted_at']; 
    public function client(){
        return $this->morphTo(__FUNCTION__,'typeClient','id_client');
    }
}