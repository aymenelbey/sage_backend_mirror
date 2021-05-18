<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactCollectivite extends Model
{
    use HasFactory,SoftDeletes;
    protected $primaryKey = "id_contact_collectivite";
    protected $fillable = [
        "id_collectivite",
        'id_contact',
        'function'
    ];
    protected $dates = ['deleted_at'];
}