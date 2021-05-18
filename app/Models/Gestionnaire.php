<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gestionnaire extends Model
{
    use HasFactory,SoftDeletes;
    protected $primaryKey = "id_gestionnaire";
    protected $fillable = [
        "status",
        "genre",
        "nom",
        "prenom",
        "telephone1",
        "telephone2",
        "mobile1",
        "mobile2",
        "email",
        "contract",
        'id_user',
        "id_admin"
    ];
    protected $dates = ['deleted_at'];
    public function sites(){
        return $this->belongsToMany(Site::class,GestionnaireHasSite::class,"id_gestionnaire","id_site")
        ->wherePivot('deleted_at', null);
    }
}