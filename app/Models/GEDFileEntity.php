<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GEDFileEntity extends Model
{
    use HasFactory;
    protected $table = 'ged_files_entities';
    protected $fillable = [
        'ged_file_id', 'entity_id', 'type', 'shareable'
    ];
    public $timestamps = true;

    public function entity(){
        
    }
}
