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
        'infos',
        'lines', 
        'valorisations',

        // 'nombreFours',
        // "capacite",
        // "nombreChaudiere",
        // "debitEau",
        // "miseEnService",
        // "typeFoursChaudiere",
        // "capaciteMaxAnu",
        // "videFour",
        // "reseauChaleur",
        // "rsCommentaire",
        // "tonnageReglementaireAp",
        // "performenceEnergetique",
        // "cycleVapeur",
        // "terboalternateur",
        // "venteProduction",
        // /****** */
        // "typeDechetRecus",
        // "traitementFumee",
        // "installationComplementair",
        // "voiTraiFemuee",
        // "traitementNOX",
        // "equipeProcessTF",
        // "reactif",
        // "typeTerboalternateur",
        // "constructeurInstallation"
    ];

        
    protected $casts = [
        'infos' => 'json',
        'lines'  => 'json', 
        'valorisations'  => 'json',
        // 'typeDechetRecus' => 'array',
        // 'typeFoursChaudiere' => 'array',
        // 'traitementFumee' => 'array',
        // 'equipeProcessTF' => 'array',
        // 'reactif' => 'array',
        // 'traitementNOX' => 'array',
        // 'installationComplementair' => 'array',
    ];


    public function dataTech()
    {
        return $this->morphOne(DataTechn::class, 'dataTech');
    }
    public function withEnums(){
        
        // $typeDech=$this->hasOne(Enemuration::class,'id_enemuration', 'typeDechetRecus')->first();
        // $trait=$this->hasOne(Enemuration::class, 'id_enemuration', 'traitementFumee')->first();
        // $install=$this->hasOne(Enemuration::class,'id_enemuration', 'installationComplementair')->first();
        // $voiTra=$this->hasOne(Enemuration::class,'id_enemuration', 'voiTraiFemuee')->first();
        // $traiNox=$this->hasOne(Enemuration::class, 'id_enemuration', 'traitementNOX')->first();
        // $equipe=$this->hasOne(Enemuration::class,'id_enemuration', 'equipeProcessTF')->first();
        // $react=$this->hasOne(Enemuration::class,'id_enemuration', 'reactif')->first();
        // $terboa=$this->hasOne(Enemuration::class, 'id_enemuration', 'typeTerboalternateur')->first();
        // $constru=$this->hasOne(Enemuration::class,'id_enemuration', 'constructeurInstallation')->first();
        // $this->typeDechetRecus = Enemuration::whereIn('id_enemuration', $this->typeDechetRecus)->get();
        // $this->traitementFumee= Enemuration::whereIn('id_enemuration', $this->traitementFumee)->get();
        // $this->installationComplementair= Enemuration::whereIn('id_enemuration', $this->installationComplementair)->get();
        // $this->voiTraiFemuee=$voiTra?$voiTra->__toString():'';
        // $this->traitementNOX= Enemuration::whereIn('id_enemuration', $this->traitementNOX)->get();
        // $this->equipeProcessTF= Enemuration::whereIn('id_enemuration', $this->equipeProcessTF)->get();
        // $this->reactif = Enemuration::whereIn('id_enemuration', $this->reactif)->get();
        // $this->typeTerboalternateur=$terboa?$terboa->__toString():'';
        // $this->constructeurInstallation=$constru?$constru->__toString():'';
        // $this->typeFoursChaudiere = Enemuration::whereIn('id_enemuration', $this->typeFoursChaudiere)->get();
    }
}