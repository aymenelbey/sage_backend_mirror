<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;


class InfoClientHistory extends Model
{
    use HasFactory;
    protected $primaryKey="id_history";
    protected $fillable = [
        'id_reference',
        'prev_value',
        'referenced_table',
        'referenced_column',
        'date_reference',
        'updated_by'
    ];
    public $timestamps = false;
    public function updated_by(){
        return $this->hasOne(Admin::class, 'id_admin', 'updated_by');
    }
    public static function customCreate(array $attributes){
        return parent::create(array_merge($attributes, ['updated_by' => Auth::user()->id]));
    }
}
