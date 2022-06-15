<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
            case 'societe_exploitants':
                $entity = SocieteExploitant::find($this->entity_id);
                break;
        }
        return $entity;
    }
    public function category(){
        return $this->hasOne(Enemuration::class, 'id_enemuration', 'category');
    }
    public function getPath(){
        return asset(Storage::url("GED/".$this->name));
    }
}
