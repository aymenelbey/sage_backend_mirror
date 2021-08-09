<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserSimple extends Model
{
    use HasFactory,SoftDeletes;
    protected $primaryKey = "id_user_simple";
    protected $fillable = [
        "email_user_sim",
        "nom",
        "prenom",
        "id_user",
        "phone",
        "created_by"
    ];
    public function user(){
        return $this->belongsTo(User::class,"id_user");
    }
}