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
        "telephone",
        "mobile",
        "email",
        "informations",
        'address'
    ];
    protected $dates = ['deleted_at'];
    public function persons_moral(){
        return $this->hasMany(ContactHasPersonMoral::class,"id_contact","id_contact");
    }
}