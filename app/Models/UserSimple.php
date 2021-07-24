<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSimple extends Model
{
    use HasFactory;
    protected $primaryKey = "id_user_simple";
    protected $fillable = [
        "email_user_sim",
        "nom",
        "prenom",
        "id_user",
        "phone",
        "created_by"
    ];
}