<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SyndicatHasEpic extends Model
{
    use HasFactory,SoftDeletes;
    protected $primaryKey = "id_syndicat_has_epic";
    protected $fillable = [
        "id_syndicat",
        "id_epic"
    ];
    protected $dates = ['deleted_at'];
}