<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\DataTechn;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use App\Traits\DeleteChecks;


class Site extends TrackableModel {
    use HasFactory, SoftDeletes, DeleteChecks;
    protected $primaryKey = "id_site";

    public $deleteChecks = ['contracts', 'ged_files', 'gestionnaire', 'exploitant', 'client'];

    protected $fillable = [
        "denomination",
        "categorieSite",
        "departement_siege",
        "region_siege",
        "sinoe",
        "adresse",
        "latitude",
        "langititude",
        "siteIntrnet",
        "telephoneStandrad",
        "anneeCreation",
        "photoSite",
        "modeGestion",
        "perdiocitRelance",
        'status',
        'updated_by',
        'status_updated_by',
        "city",
        "country",
        "postcode",
    ];

    protected $dates = ['deleted_at'];
    public function dataTech(){
        return $this->hasOne(DataTechn::class,"id_site","id_site");
    }
    public function contracts(){
        return $this->hasMany(Contrat::class,"id_site");
    }
    public function photos(){
        return $this->hasMany(ImageSage::class,"ref_id","id_site");
    }
    public function gestionnaire(){
        return $this->hasOneThrough(Gestionnaire::class,GestionnaireHasSite::class,"id_site","id_gestionnaire","id_site","id_gestionnaire");
    }
    public function client(){
        return $this->hasOneThrough(Collectivite::class,ClientHasSite::class,"id_site","id_collectivite","id_site","id_collectivite");
    }
    public function exploitant(){
        return $this->hasOne(SocieteExpSite::class,"id_site");
    }
    public function departement_siege(){
        return $this->hasOne(Departement::class,'id_departement', 'departement_siege');
    }
    public function region_siege(){
        return $this->hasOne(Region::class,'id_region', 'region_siege');
    }
    public function updated_by(){
        return $this->hasOne(Admin::class, 'id_admin', 'updated_by');
    }
    public function status_updated_by(){
        return $this->hasOne(Admin::class,'id_admin', 'status_updated_by');
    }
    public function ged_files(){
        return GEDFile::with('category')->where('type', 'sites')->where('entity_id', $this->id_site);
    }
    public function files($category = null){
        $mapping = ['Syndicat' => 'syndicats', 'Epic' => 'epics','EPIC' => 'epics', 'Commune' => 'communes', 'Societe' => 'societe_exploitants'];
        $ids_mapping = ['Syndicat' => 'id_syndicat', 'Epic' => 'id_epic', 'EPIC' => 'id_epic' ,'Commune' => 'id_commune', 'Societe' => 'id_societe_exploitant'];
        $query = (new GEDFile())->newQuery();
        $query = $query->with(['category']);

        $exploitant = $this->exploitant()->first();
        $client = $this->client()->first();


        if($category && !empty($category)){
            $query = $query->whereHas('category', function ($query) use ($category){
                return $query->whereIn('code', array_values($category));
            });
        }

        $query = $query->where(function($query) use ($mapping, $ids_mapping, $exploitant, $client) {
            return $query->where([
                    ['type', '=','sites'],
                    ['entity_id', '=',$this->id_site]
                ])->orWhere([
                    ['type', '=', $mapping[$exploitant->typeExploitant] ],
                    ['entity_id', '=', $exploitant->id_client ]
                ])->orWhere([
                    ['type', '=', $mapping[$client->typeCollectivite]],
                    ['entity_id', '=', $client->client[$ids_mapping[$client->typeCollectivite]] ]
                ]);
        });

        return $query;
    }
}