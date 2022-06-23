<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DeleteChecks;

class Contrat extends TrackableModel
{
    use HasFactory, SoftDeletes, DeleteChecks;
    protected $primaryKey = "id_contrat";
    public $deleteChecks = ['site', 'contractant', 'communes'];


    protected $fillable = [
        "dateDebut",
        'dateFin',
        "autreActivite",
        "id_site",
        "contractant",
        'updated_by',
    ];
    protected $dates = ['deleted_at'];
    public function site(){
        return $this->hasOne(Site::class,"id_site","id_site")->withDefault();
    }
    public function contractant(){
        return $this->hasOne(SocieteExploitant::class,"id_societe_exploitant","contractant")
        ->withDefault();
    }
    public function communes(){
        return $this->hasMany(CommunHasContrat::class,'id_contrat','id_contrat');
    }
    public function updated_by(){
        return $this->hasOne(Admin::class, 'id_admin', 'updated_by');
    }
}