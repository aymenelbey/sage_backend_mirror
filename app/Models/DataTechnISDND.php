<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataTechnISDND extends Model
{
    use HasFactory;
    protected $primaryKey = "id_data_isdnd";
    protected $table='data_techn_isdnds';
    protected $fillable = [
        "capaciteNominale",
        "capaciteRestante",
        "capaciteReglementaire",
        "projetExtension",
        "dateExtension",
        "dateOuverture",
        "dateFermeture",
        "dateFermeturePrev"
    ];
    public function dataTech()
    {
        return $this->morphOne(DataTechn::class, 'dataTech');
    }
}