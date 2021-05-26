<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PersonFunction extends Model
{
    use HasFactory,SoftDeletes;
    protected $primaryKey = "id_person_function";
    protected $fillable = [
        "functionPerson",
        "id_person",
        "status"
    ];
    protected $dates = ['deleted_at'];
    public function getFunctionStringAttribute()
    {
        $func=$this->hasOne(Enemuration::class,'id_enemuration', 'functionPerson')->first();
        return $func->__toString();
    }
}