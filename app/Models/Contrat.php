<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contrat extends Model
{
    use HasFactory,SoftDeletes;
    protected $primaryKey = "id_contrat";
    protected $fillable = [
        "dateDebut",
        'dateFin',
        "autreActivite",
        "id_site",
        "contractant"
    ];
    protected $dates = ['deleted_at'];
    public function site(){
        return $this->hasOne(Site::class,"id_site","id_site");
    }
    public function contractant(){
        return $this->hasOne(SocieteExploitant::class,"id_societe_exploitant","contractant")
        ->withDefault();
    }
    public function communes(){
        return $this->hasMany(CommunHasContrat::class,'id_contrat','id_contrat');
    }
}