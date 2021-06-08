<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class CompetanceDechet extends Model
{
    use HasFactory,SoftDeletes;
    protected $primaryKey="id_competance_dechet";
    protected $fillable = [
        'code',
        'start_date',
        'end_date',
        'comment',
        'owner_competance',
        'delegue_competance',
        'delegue_type',
        'owner_type',
        'competence_dechet'
    ];
    protected $dates = ['deleted_at'];
    protected $appends =['competence_dechet_name'];
    public function getCompetenceDechetNameAttribute(){
        return $this->hasOne(Enemuration::class, 'id_enemuration', 'competence_dechet')->first()->__toString();
    }
    public function delegue_competance(){
        return $this->morphTo(__FUNCTION__,'delegue_type','delegue_competance');
    }
    public function owner_competance(){
        return $this->morphTo(__FUNCTION__,'owner_type','owner_competance');
    }
    protected static function booted()
    {
        static::retrieved(function ($model) {
            $model->start_date=Carbon::parse($model->start_date)->format('d/m/y');
            $model->end_date=Carbon::parse($model->end_date)->format('d/m/y');
        });
    }
}