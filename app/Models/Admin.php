<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Auth\Passwords\CanResetPassword;

class Admin extends Model implements CanResetPasswordContract
{
    use HasFactory,Notifiable,SoftDeletes,CanResetPassword;
    protected $primaryKey = "id_admin";
    protected $fillable = [
        "email",
        'nom',
        'prenom',
        'phone',
        'id_user'
    ];
    protected $dates = ['deleted_at'];
}