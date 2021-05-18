<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunHasContrat extends Model
{
    use HasFactory,SoftDeletes;
    protected $primaryKey = "id_commun_has_contrat";
    protected $fillable = [
        "id_contrat",
        "id_commune"
    ];
    protected $dates = ['deleted_at'];
}