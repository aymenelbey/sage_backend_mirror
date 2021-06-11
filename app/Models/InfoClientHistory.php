<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfoClientHistory extends Model
{
    use HasFactory;
    protected $primaryKey="id_history";
    protected $fillable = [
        'id_reference',
        'prev_value',
        'referenced_table',
        'referenced_column',
        'date_reference'
    ];
    public $timestamps = false;
}
