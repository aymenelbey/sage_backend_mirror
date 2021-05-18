<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shared extends Model
{
    use HasFactory;
    protected $primaryKey = "id_shared";
    protected $fillable = [
        "duree",
        'id_user_premieum',
        "id_admin",
        "id_site"
    ];
    public function dbshared(){
        return $this->belongsTo(DbShared::class,"id_shared");
    }
}
