<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory,SoftDeletes;
    protected $primaryKey = "id_contact";
    protected $fillable = [
        "status",
        "genre",
        "nom",
        "prenom",
        "telephone1",
        "telephone2",
        "mobile1",
        "mobile2",
        "email",
        "informations",
        'address'
    ];
    protected $dates = ['deleted_at'];
    public function personsMoral(){
        return $this->hasMany(ContactHasPersonMoral::class,"id_contact","id_contact");
    }
}