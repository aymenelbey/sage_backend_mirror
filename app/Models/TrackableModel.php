<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

class TrackableModel extends Model
{
    use HasFactory;
    public $timestamps = true;
    public static $VALID_STATUS = ['VALIDATED', 'NOT_VALIDATED', 'NOT_PUBLISHED'];

    protected $fillable = [
        'status', 'updated_by', 'status_updated_by'
    ];

    public function update(array $attributes = [], array $options = []){
        $added_attributes = [
            'updated_by' => Auth::user()->id
        ];
        
        if(isset($attributes['status']) && $this->status != $attributes['status']){
            $added_attributes['status_updated_by'] = Auth::user()->id;
        }

        if(parent::update(array_merge($attributes, $added_attributes), $options)){
            return true;
        }

        return false;
    }

    public function status_updated_by(){
        return $this->hasOne(Admin::class,'id_admin', 'status_updated_by');
    }

}
