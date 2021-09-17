<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable,SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'password',
        'init_password',
        "typeuser",
        "picture"
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $appends = [
        'user_channel',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'username_verified_at' => 'datetime',
    ];
    protected $dates = ['deleted_at'];
    public function userType()
    {
        return $this->morphTo(__FUNCTION__, 'typeuser', 'id','id_user');
    }

    public function getUserChannelAttribute()
    {
        return sha1($this->id);
    }

    public function getJWTIdentifier(){
        return $this->getKey();
    }

    public function getJWTCustomClaims(){
        return [];
    }

    public function evaluGrids(){
        return $this->hasMany(EvaluationGrid::class);
    }

    static function getUsername($firstName,$lastName)
    {
        $username = $firstName."_".$lastName;

        $i = 0;
        while(User::whereUsername($username)->exists())
        {
            $i++;
            $username =$firstName."_".$lastName.'.'.$i;
        }
        return $username;
    }
    
    protected static function booted()
    {
        static::retrieved(function ($model) {
            if($model->picture){
                $model->picture=asset($model->picture);
            }
        });
    }
}