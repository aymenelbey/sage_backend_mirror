<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GestionnaireHasSite extends Model
{
    use HasFactory,SoftDeletes;
    protected $primaryKey = "id_gestionnaire_has_sites";
    protected $fillable = [
        'id_admin',
        'id_gestionnaire',
        'id_site'
    ];
    protected $dates = ['deleted_at'];
}