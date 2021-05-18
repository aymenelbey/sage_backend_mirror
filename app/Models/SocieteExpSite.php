<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class SocieteExpSite extends Model
{
    use HasFactory,SoftDeletes;

    protected $primaryKey = "id_societe_exp_site";
    protected $fillable = [
        "typeExploitant",
        'id_site',
        'id_client'
    ];
    protected $dates = ['deleted_at'];
    public function client(){
        return  $this->morphTo(__FUNCTION__,'typeExploitant', 'id_client');
    }
}