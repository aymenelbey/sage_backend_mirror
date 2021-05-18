<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataTechnUVE extends Model
{
    use HasFactory;
    protected $primaryKey = "id_data_uve";
    protected $table = "data_techn_uves";
    protected $fillable = [
        'nombreFours',
        "capacite",
        "nombreChaudiere",
        "debitEau",
        "miseEnService",
        "typeFoursChaudiere",
        "capaciteMaxAnu",
        "videFour",
        "reseauChaleur",
        "rsCommentaire",
        "tonnageReglementaireAp",
        "performenceEnergetique",
        "cycleVapeur",
        "terboalternateur",
        "venteProduction",
        /****** */
        "typeDechetRecus",
        "traitementFumee",
        "installationComplementair",
        "voiTraiFemuee",
        "traitementNOX",
        "equipeProcessTF",
        "reactif",
        "typeTerboalternateur",
        "constructeurInstallation"
    ];
    public function dataTech()
    {
        return $this->morphOne(DataTechn::class, 'dataTech');
    }
}