<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class Enemuration extends Model
{
    use HasFactory;
    protected $primaryKey = "id_enemuration";
    protected $fillable = [
        "key_enum",
        "value_enum",
        "code"
    ];

    public function __toString()
    {
        return $this->value_enum;
    }
    public function canDelete(){
        $values = [
            'groupeList' => [
                'index' => 'id_societe_exploitant',
                'table' => 'societe_exploitants',
                'type' => 'single',
                'condition' => function($value){
                    return "(groupe)::jsonb @> '$value'";
                }   
            ],
            'typeDechetRecus' => [
                'index' => 'id_data_uve',
                'table' => 'data_techn_uves',
                'type' => 'single',
                'condition' => function($value){
                    return "(infos->'typeDechetRecus')::jsonb @> '[$value]'";
                }
            ],
            'informationComplementaire' => [
                'index' => 'id_data_uve',
                'table' => 'data_techn_uves',
                'type' => 'single',
                'condition' =>  function($value){
                    return "(infos->'informationComplementaire')::jsonb @> '[$value]'";
                }
            ],
            'typeFours' => [
                'index' => 'id_data_uve',
                'table' => 'data_techn_uves',
                'type' => 'array',
                'col' => 'lines',
                'condition' => function($value){
                    return "(element->'typeFours')::jsonb @> '[$value]';";
                }
            ],
            'typeFours' => [
                'index' => 'id_data_uve',
                'table' => 'data_techn_uves',
                'type' => 'array',
                'col' => 'lines',
                'condition' => function($value){
                    return "(element->'typeFours')::jsonb @> '[$value]';";
                }
            ],
            'constructeurInstallation' => [
                'index' => 'id_data_uve',
                'table' => 'data_techn_uves',
                'type' => 'array',
                'col' => 'lines',
                'condition' => function($value){
                    return "(element->'constructeurInstallation')::jsonb @> '[$value]';";
                }
            ],
            'typeChaudiere' => [
                'index' => 'id_data_uve',
                'table' => 'data_techn_uves',
                'type' => 'array',
                'col' => 'lines',
                'condition' => function($value){
                    return "(element->'typeChaudiere')::jsonb @> '[$value]';";
                }
            ],
            'constructeurChaudiere' => [
                'index' => 'id_data_uve',
                'table' => 'data_techn_uves',
                'type' => 'array',
                'col' => 'lines',
                'condition' => function($value){
                    return "(element->'constructeurChaudiere')::jsonb @> '[$value]';";
                }
            ],
            'traitementFumee' => [
                'index' => 'id_data_uve',
                'table' => 'data_techn_uves',
                'type' => 'array',
                'col' => 'lines',
                'condition' => function($value){
                    return "(element->'traitementFumee')::jsonb @> '[$value]';";
                }
            ],
            'equipeProcessTF' => [
                'index' => 'id_data_uve',
                'table' => 'data_techn_uves',
                'type' => 'array',
                'col' => 'lines',
                'condition' => function($value){
                    return "(element->'equipeProcessTF')::jsonb @> '[$value]';";
                }
            ],
            'reactif' => [
                'index' => 'id_data_uve',
                'table' => 'data_techn_uves',
                'type' => 'array',
                'col' => 'lines',
                'condition' => function($value){
                    return "(element->'reactif')::jsonb @> '[$value]';";
                }
            ],
            'traitementNOX' => [
                'index' => 'id_data_uve',
                'table' => 'data_techn_uves',
                'type' => 'array',
                'col' => 'lines',
                'condition' => function($value){
                    return "(element->'traitementNOX')::jsonb @> '[$value]';";
                }
            ],
            'reactifDENOX' => [
                'index' => 'id_data_uve',
                'table' => 'data_techn_uves',
                'type' => 'array',
                'col' => 'lines',
                'condition' => function($value){
                    return "(element->'reactifDENOX')::jsonb @> '[$value]';";
                }
            ],
            'installationComplementair' => [
                'index' => 'id_data_uve',
                'table' => 'data_techn_uves',
                'type' => 'array',
                'col' => 'lines',
                'condition' => function($value){
                    return "(element->'installationComplementair')::jsonb @> '[$value]';";
                }
            ],
            'agregateurElectrique' => [
                'index' => 'id_data_uve',
                'table' => 'data_techn_uves',
                'type' => 'single',
                'condition' => function($value){
                    return "(valorisations->'agregateurElectrique')::jsonb @> '$value'";
                }
            ],

            'typeEquipement' => [
                'index' => 'id_data_uve',
                'table' => 'data_techn_uves',
                'type' => 'array',
                'col' => "valorisations->'blocks'",
                'condition' => function($value){
                    return "(element->'typeEquipement')::jsonb @> '$value';";
                }
            ],
            'marqueEquipement' => [
                'index' => 'id_data_uve',
                'table' => 'data_techn_uves',
                'type' => 'array',
                'col' => "valorisations->'blocks'",
                'condition' => function($value){
                    return "(element->'marqueEquipement')::jsonb @> '$value';";
                }
            ],
            'RCUIndustirel' => [
                'index' => 'id_data_uve',
                'table' => 'data_techn_uves',
                'type' => 'array',
                'col' => "valorisations->'blocks'",
                'condition' => function($value){
                    return "(element->'RCUIndustirel')::jsonb @> '$value';";
                }
            ],
            'clients' => [
                'index' => 'id_data_uve',
                'table' => 'data_techn_uves',
                'type' => 'array',
                'col' => "valorisations->'blocks'",
                'condition' => function($value){
                    return "(element->'client')::jsonb @> '[$value]';";
                }
            ],
        ];
        
        $item = $values[$this->key_enum];
        if($item['type'] == 'single'){
            $condition = $item['condition']($this->id_enemuration);
            $subquery = DB::select(DB::raw("SELECT count({$item['index']}) as count FROM {$item['table']} where {$condition}"));
            if($subquery[0]->count > 0){
                return false;
            }   
        }
        else if($item['type'] == 'array'){
            $query = "select count(*) from (select json_array_elements({$item['col']}) as element from data_techn_uves where {$item['col']} is not null) as tmp where ".$item['condition']($this->id_enemuration);
            $subquery = DB::select($query);
            print_r([
                'query' => $query,
                'subquery' => $subquery
            ]);
            if($subquery[0]->count > 0){
                return false;
            }      
        }
        return true;
    }
}