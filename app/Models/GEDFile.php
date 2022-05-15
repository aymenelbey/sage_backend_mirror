<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GEDFile extends Model
{
    use HasFactory;
    protected $table = 'ged_files';

    protected $fillable = [
        'name', 'date', 'category', 'type', 'entity_id', 'shareable'
    ];
    public function entity(){
        $entity = null;
        switch($this->type){
            case 'sites':
                $entity = Site::find($this->entity_id);
                break;
            case 'syndicats':
                $entity = Syndicat::find($this->entity_id);
                break;
            case 'epics':
                $entity = EPIC::find($this->entity_id);
                break;
            case 'communes':
                $entity = Commune::find($this->entity_id);
                break;
            case 'societies':
                $entity = SocieteExploitant::find($this->entity_id);
                break;
        }
        return $entity;
        // $query->leftJoin('sites', function($query){
        //     $query->where('ged_files_entities.type', 'sites');
        //     $query->on('ged_files_entities.entity_id', '=', 'sites.id_site');
        // });

        // $query->leftJoin('epics', function($query){
        //     $query->where('ged_files_entities.type', 'epics');
        //     $query->on('ged_files_entities.entity_id', '=', 'epics.id_epic');
        // });

        // $query->leftJoin('communes', function($query){
        //     $query->where('ged_files_entities.type', 'communes');
        //     $query->on('ged_files_entities.entity_id', '=', 'communes.id_commune');
        // });

        // $query->leftJoin('syndicats', function($query){
        //     $query->where('ged_files_entities.type', 'syndicats');
        //     $query->on('ged_files_entities.entity_id', '=', 'syndicats.id_syndicat');
        // });

        // $query->leftJoin('societe_exps', function($query){
        //     $query->where('ged_files_entities.type', 'societe_exps');
        //     $query->on('ged_files_entities.entity_id', '=', 'societe_exps.id_societe_exp');
        // });

        // return $query->get();
    }
    public function category(){
        return $this->hasOne(Enemuration::class, 'id_enemuration', 'category');
    }
}
