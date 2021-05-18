<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Admin extends Model
{
    use HasFactory,SoftDeletes;
    protected $primaryKey = "id_admin";
    protected $fillable = [
        "email_admin",
        'nom',
        'prenom',
        'phone',
        'id_user'
    ];
    protected $dates = ['deleted_at'];
}