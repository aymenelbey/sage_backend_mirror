<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}