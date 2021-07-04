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
    public function withEnums(){
        $typeDech=$this->hasOne(Enemuration::class,'id_enemuration', 'typeDechetRecus')->first();
        $trait=$this->hasOne(Enemuration::class, 'id_enemuration', 'traitementFumee')->first();
        $install=$this->hasOne(Enemuration::class,'id_enemuration', 'installationComplementair')->first();
        $voiTra=$this->hasOne(Enemuration::class,'id_enemuration', 'voiTraiFemuee')->first();
        $traiNox=$this->hasOne(Enemuration::class, 'id_enemuration', 'traitementNOX')->first();
        $equipe=$this->hasOne(Enemuration::class,'id_enemuration', 'equipeProcessTF')->first();
        $react=$this->hasOne(Enemuration::class,'id_enemuration', 'reactif')->first();
        $terboa=$this->hasOne(Enemuration::class, 'id_enemuration', 'typeTerboalternateur')->first();
        $constru=$this->hasOne(Enemuration::class,'id_enemuration', 'constructeurInstallation')->first();
        $this->typeDechetRecus=$typeDech?$typeDech->__toString():'';
        $this->traitementFumee=$trait?$trait->__toString():'';
        $this->installationComplementair=$install?$install->__toString():'';
        $this->voiTraiFemuee=$voiTra?$voiTra->__toString():'';
        $this->traitementNOX=$traiNox?$traiNox->__toString():'';
        $this->equipeProcessTF=$equipe?$equipe->__toString():'';
        $this->reactif=$react?$react->__toString():'';
        $this->typeTerboalternateur=$terboa?$terboa->__toString():'';
        $this->constructeurInstallation=$constru?$constru->__toString():'';
    }
}