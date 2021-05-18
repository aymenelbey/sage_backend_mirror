<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShareSite extends Model
{
    use HasFactory,SoftDeletes;
    protected $primaryKey = "id_share_site";
    protected $fillable = [
        'start',
        'end',
        'columns',
        'id_user_premieum',
        'id_site',
        'id_admin',
        'is_blocked'
    ];
    protected $dates = ['deleted_at'];
    public function site(){
        return $this->hasOne(Site::class,"id_site","id_site");
    }
}