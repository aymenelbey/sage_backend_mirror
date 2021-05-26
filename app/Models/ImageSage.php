<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \App\Http\Traits\UsesUuid;

class ImageSage extends Model
{
    use HasFactory,SoftDeletes,UsesUuid;
    public $incrementing = false;
    protected $primaryKey = "uid";
    protected $fillable = [
        'name',
        'url',
        'ref_id',
        "status"
    ];
    protected $dates = ['deleted_at'];
    public function __toString()
    {
        return $this->url;
    }
    /*public function toArray()
    {
        return $this->url;
    }*/
}