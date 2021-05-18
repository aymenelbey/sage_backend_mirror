<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class UserPremieum extends Model
{
    use HasFactory,SoftDeletes;
    protected $primaryKey = "id_user_premieum";
    protected $fillable = [
        "email_user_prem",
        "isPaid",
        "nom",
        "prenom",
        "lastPaiment",
        "phone",
        "NbUserCreated",
        "nbAccess",
        "created_by",
        'id_user'
    ];
    protected $dates = ['deleted_at'];       
}