<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataTechn extends Model
{
    use HasFactory;
    protected $primaryKey = "id_data_techn";
    protected $fillable = [
        "id_site",
        "typesite",
        "id_data_tech"
    ];
    public function dataTech()
    {
        return $this->morphTo(__FUNCTION__, 'typesite', 'id_data_tech');
    }
}