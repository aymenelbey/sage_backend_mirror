<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientHasSite extends Model
{
    use HasFactory,SoftDeletes;
    protected $primaryKey = "id_client_has_sites";
    protected $fillable = [
        "id_site",
        "id_collectivite"
    ];
    protected $dates = ['deleted_at'];
}