<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DeleteChecks;


class Gestionnaire extends Model
{
    use HasFactory, SoftDeletes, DeleteChecks;
    protected $primaryKey = "id_gestionnaire";

    public $deleteChecks = ['sites', 'user'];

    protected $fillable = [
        "status",
        "genre",
        "nom",
        "prenom",
        "telephone",
        "mobile",
        "email",
        "societe",
        'id_user',
        "id_admin"
    ];
    protected $dates = ['deleted_at'];
    public function sites(){
        return $this->belongsToMany(Site::class,GestionnaireHasSite::class,"id_gestionnaire","id_site")
        ->wherePivot('deleted_at', null);
    }
    public function user(){
        return $this->belongsTo(User::class,"id_user");
    }
}