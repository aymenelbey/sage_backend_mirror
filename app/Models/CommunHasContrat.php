<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunHasContrat extends Model
{
    use HasFactory,SoftDeletes;
    protected $primaryKey = "id_commun_has_contrat";
    protected $fillable = [
        "id_contrat",
        "idPersonMoral",
        "typePersonMoral"
    ];
    protected $dates = ['deleted_at'];
    protected $with = ['person'];
    public function person()
    {
        return $this->morphTo(__FUNCTION__, 'typePersonMoral', 'idPersonMoral');
    }
    public function toArray(){
        if($this->person){
            return [
                'id_person'=> $this->person->id_person,
                'typePersonMoral'=>$this->person->typePersonMoral,
                'name'=>$this->person->name,
                'dataIndex'=>$this->person->dataIndex,
                'adresse'=>$this->person->adresse,
                'city' => $this->person->city,
                $this->person->dataIndex=>$this->person[$this->person->dataIndex]
            ];
        }else{
            return [
                'id_person'=>  '',
                'typePersonMoral'=> '',
                'name'=> '',
                'city' => '',
                'dataIndex'=> '',
                'adresse'=> '',
            ];
        }
    }
}