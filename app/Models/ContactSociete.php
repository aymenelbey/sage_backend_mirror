<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactSociete extends Model
{
    use HasFactory;
    protected $primaryKey = "id_contact_societe";
    protected $fillable = [
        "id_societe_exploitant",
        'id_contact',
        'function'
    ];
}