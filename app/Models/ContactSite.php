<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactSite extends Model
{
    use HasFactory;
    protected $primaryKey = "id_contact_site";
    protected $fillable = [
        "id_site",
        'id_contact'
    ];
}
