<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataTechnTRI extends Model
{
    use HasFactory;
    protected $primaryKey = "id_data_tri";
    protected $table = "data_techn_tris";
    protected $fillable = [
        "capaciteHoraire",
        "capaciteNominale",
        "capaciteReglementaire",
        "dateExtension",
        "miseEnService",
        "dernierConstructeur",
        /**** */
        "extension"
    ];
    public function dataTech()
    {
        return $this->morphOne(DataTechn::class, 'dataTech');
    }
    public function withEnums(){
        $extension=$this->hasOne(Enemuration::class,'id_enemuration', 'extension')->first();
        $this->extension=$extension?$extension->__toString():'';
    }
}