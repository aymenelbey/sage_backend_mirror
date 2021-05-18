<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataTechnTMB extends Model
{
    use HasFactory;
    protected $primaryKey = "id_data_tmb";
    protected $table = "data_techn_tmbs";
    protected $fillable = [
        "quantiteRefus",
        "CSRProduit",
        "envoiPreparation",
        "tonnageAnnuel",
        "capaciteNominal",
        "dernierConstruct",
        /********* */
        "typeInstallation",
        "typeDechetAccepter",
        "technologie",
        "valorisationEnergitique",
        "autreActivite"
    ];
    public function dataTech()
    {
        return $this->morphOne(DataTechn::class, 'dataTech');
    }
}