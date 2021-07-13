<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeSharedSite extends Model
{
    use HasFactory;
    protected $primaryKey = "id_type_shared_site";
    protected $fillable = [
        'site_categorie',
        'id_share_site'
    ];
}