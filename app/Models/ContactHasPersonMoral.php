<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactHasPersonMoral extends Model
{
    use HasFactory,SoftDeletes;
    protected $primaryKey = "id_contact_has_person_morals";
    protected $fillable = [
        "idPersonMoral",
        "id_contact",
        "typePersonMoral",
        "function"
    ];
    protected $dates = ['deleted_at'];

    public function person()
    {
        return $this->morphTo(__FUNCTION__, 'typePersonMoral', 'idPersonMoral');
    }
}