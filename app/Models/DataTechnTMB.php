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
    public function withEnums(){
        $typeInstal=$this->hasOne(Enemuration::class,'id_enemuration', 'typeInstallation')->first();
        $dechetaccept=$this->hasOne(Enemuration::class, 'id_enemuration', 'typeDechetAccepter')->first();
        $technologie=$this->hasOne(Enemuration::class,'id_enemuration', 'technologie')->first();
        $valorisation=$this->hasOne(Enemuration::class,'id_enemuration', 'valorisationEnergitique')->first();
        $autreActi=$this->hasOne(Enemuration::class, 'id_enemuration', 'autreActivite')->first();
        $this->typeInstallation=$typeInstal?$typeInstal->__toString():'';
        $this->typeDechetAccepter=$dechetaccept?$dechetaccept->__toString():'';
        $this->technologie=$technologie?$technologie->__toString():'';
        $this->valorisationEnergitique=$valorisation?$valorisation->__toString():'';
        $this->autreActivite=$autreActi?$autreActi->__toString():'';
    }
}