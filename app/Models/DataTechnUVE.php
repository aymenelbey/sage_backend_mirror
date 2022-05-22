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

        
    protected static function booted(){
        static::retrieved(function ($model) {
            $model->infos = $model->infos ? $model->infos : json_decode('{}', 1); 
            $model->lines = $model->lines ? $model->lines : []; 
            $model->valorisations = $model->valorisations ? $model->valorisations : json_decode('{"blocks": []}', 1); 
        });
    }
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
        
        $infos = ['infos' => [], 'lines' => [], 'valorisations' => $this->valorisations];
        $infos['valorisations']['blocks'] = [];

        if(isset($this->infos['typeDechetRecus'])){
            $infos['infos']['typeDechetRecus'] = array_map(function($enum){
                return $enum['value_enum'];
            }, Enemuration::whereIn('id_enemuration', $this->infos['typeDechetRecus'])->get()->toArray());
        }

        foreach($this->lines as $line){
            
            if(isset($line['constructeurChaudiere'])){
                $line['constructeurChaudiere'] = Enemuration::where('id_enemuration', $line['constructeurChaudiere'])->first()->value_enum;
            }

            if(isset($line['constructeurInstallation'])){
                $line['constructeurInstallation'] = Enemuration::where('id_enemuration', $line['constructeurInstallation'])->first()->value_enum;
            }

            if(isset($line['equipeProcessTF'])){
                $line['equipeProcessTF'] = array_map(function($enum){
                    return $enum['value_enum'];
                }, Enemuration::whereIn('id_enemuration', $line['equipeProcessTF'])->get()->toArray());
            }
            
            if(isset($line['installationComplementair'])){
                $line['installationComplementair'] = array_map(function($enum){
                    return $enum['value_enum'];
                }, Enemuration::whereIn('id_enemuration', $line['installationComplementair'])->get()->toArray());
            }
            
            if(isset($line['reactif'])){
                $line['reactif'] = array_map(function($enum){
                    return $enum['value_enum'];
                }, Enemuration::whereIn('id_enemuration', $line['reactif'])->get()->toArray());
            }

            if(isset($line['traitementFumee'])){
                $line['traitementFumee'] = array_map(function($enum){
                    return $enum['value_enum'];
                }, Enemuration::whereIn('id_enemuration', $line['traitementFumee'])->get()->toArray());
            }

            if(isset($line['traitementNOX'])){
                $line['traitementNOX'] = array_map(function($enum){
                    return $enum['value_enum'];
                }, Enemuration::whereIn('id_enemuration', $line['traitementNOX'])->get()->toArray());
            }
            $infos['lines'][] = $line;
        }

        foreach($this->valorisations['blocks'] as $block){
            
            if(isset($block['marqueEquipement'])){

                if(is_array($block['marqueEquipement'])){
                    $block['marqueEquipement'] = array_map(function($enum){
                        return $enum['value_enum'];
                    }, Enemuration::whereIn('id_enemuration', $block['marqueEquipement'])->get()->toArray());
                }else{
                    $block['marqueEquipement'] = Enemuration::where('id_enemuration', $block['marqueEquipement'])->first()->value_enum;
                }

            }
            
            if(isset($block['typeEquipement'])){
                if(is_array($block['typeEquipement'])){
                    $block['typeEquipement'] = array_map(function($enum){
                        return $enum['value_enum'];
                    }, Enemuration::whereIn('id_enemuration', $block['typeEquipement'])->get()->toArray());
                }else{
                    $block['typeEquipement'] = Enemuration::where('id_enemuration', $block['typeEquipement'])->first()->value_enum;
                }
            }
            
            if(isset($block['RCUIndustirel'])){
                if(is_array($block['RCUIndustirel'])){
                    $block['RCUIndustirel'] = array_map(function($enum){
                        return $enum['value_enum'];
                    }, Enemuration::whereIn('id_enemuration', $block['RCUIndustirel'])->get()->toArray());
                }else{
                    $block['RCUIndustirel'] = Enemuration::where('id_enemuration', $block['RCUIndustirel'])->first()->value_enum;
                }
            }
            
            if(isset($block['client'])){
                $block['client'] = array_map(function($enum){
                    return $enum['value_enum'];
                }, Enemuration::whereIn('id_enemuration', $block['client'])->get()->toArray());
            }

            $infos['valorisations']['blocks'][] = $block;
        }
        return $infos;
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