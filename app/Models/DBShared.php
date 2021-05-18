<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DbShared extends Model
{
    use HasFactory;
    protected $primaryKey = "id_db_shared";
    protected $fillable = [
        "id_shared",
        "nomTable",
        "columnName"
    ];
}
