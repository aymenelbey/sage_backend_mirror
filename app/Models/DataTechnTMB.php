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
    
    protected $casts = [
        'technologie' => 'array',
        'valorisationEnergitique' => 'array',
        'autreActivite' => 'array',
        'typeDechetAccepter' => 'array',
    ];

    public function dataTech()
    {
        return $this->morphOne(DataTechn::class, 'dataTech');
    }
    public function withEnums(){
        $typeInstal=$this->hasOne(Enemuration::class,'id_enemuration', 'typeInstallation')->first();
        
        $technologie = Enemuration::whereIn('id_enemuration', is_array($this->technologie) ? $this->technologie : [$this->technologie])->get();
        $valorisation = Enemuration::whereIn('id_enemuration', is_array($this->valorisationEnergitique) ? $this->valorisationEnergitique : [$this->valorisationEnergitique])->get();
        $autreActi= Enemuration::whereIn('id_enemuration', is_array($this->autreActivite) ? $this->autreActivite : [$this->autreActivite])->get();
        $dechetaccept= Enemuration::whereIn('id_enemuration', is_array($this->typeDechetAccepter) ? $this->typeDechetAccepter : [$this->typeDechetAccepter])->get();

        $this->typeInstallation=$typeInstal?$typeInstal->__toString():'';
        
        $this->typeDechetAccepter= $dechetaccept ;
        $this->technologie= $technologie ;
        $this->valorisationEnergitique = $valorisation;
        $this->autreActivite= $autreActi ;
    }
    
}