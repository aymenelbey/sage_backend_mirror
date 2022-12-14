<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DeleteChecks;

use App\Http\Helpers\ToolHelper;
use Carbon\Carbon;



class EPIC extends TrackableModel
{
    use HasFactory, DeleteChecks;
    protected $primaryKey = "id_epic";
    public $deleteChecks = ['contacts', 'communes', 'syndicat', 'sites', 'files', 'competance_recu', 'competance_exercee', 'competance_delegue'];

    protected $table = "epics";
    protected $fillable = [
        "nomEpic",
        "serin",
        "siret",
        "adresse",
        'lat',
        'lang',
        'nom_court',
        'sinoe',
        "siteInternet",
        "telephoneStandard",
        "nombreHabitant",
        "logo",
        "nature_juridique",
        "departement_siege",
        "region_siege",
        "date_enter",
        "city",
        "country",
        "postcode",
        "id_collectivite",
        "id_syndicat",
        'updated_by',
        'status',
        'status_updated_by'
    ];

    protected $dates = ['deleted_at'];
    protected $appends = ['typePersonMoral','dataIndex','id_person','name'];
    public function getTypePersonMoralAttribute(){
        return "Epic";
    }
    public function getIdPersonAttribute(){
        return $this->id_epic;
    }
    public function getDataIndexAttribute(){
        return "nomEpic";
    }
    public function getNameAttribute(){
        return "Nom EPIC";
    }
    public function contacts(){
        return $this->belongsToMany(Contact::class, ContactHasPersonMoral::class,'idPersonMoral','id_contact','id_epic','id_contact')
        ->wherePivot('typePersonMoral', 'EPIC')
        ->wherePivot('deleted_at', null);
    }
    public function communes(){
        return $this->hasMany(Commune::class,"id_epic");
    }
    public function syndicat(){
        return $this->hasOneThrough(Syndicat::class, SyndicatHasEpic::class,'id_epic','id_syndicat','id_epic','id_syndicat');
    }
    public function logo(){
        return $this->hasMany(ImageSage::class,"uid","logo");
    }
    /* competances */
    public function competance_exercee(){
        return $this->hasMany(CompetanceDechet::class,'owner_competance', 'id_epic')
        ->where('owner_type','EPIC')
        ->whereNull('delegue_competance');
    }
    public function competance_delegue(){
        return $this->hasMany(CompetanceDechet::class,'owner_competance', 'id_epic')
        ->with('delegue_competance')
        ->where('owner_type','EPIC')
        ->whereNotNull('delegue_competance');
    }
    public function competance_recu(){
        return $this->hasMany(CompetanceDechet::class,'delegue_competance', 'id_epic')
        ->with('owner_competance')
        ->where('delegue_type','Epic');
    }
    /* end competances */
    public function region_siege(){
        return $this->hasOne(Region::class,'id_region', 'region_siege');
    }
    public function nature_juridique(){
        return $this->hasOne(Enemuration::class,'id_enemuration', 'nature_juridique');
    }
    public function departement_siege(){
        return $this->hasOne(Departement::class,'id_departement', 'departement_siege');
    }
    public function sites(){
        return $this->hasManyThrough(Site::class,ClientHasSite::class,'id_collectivite','id_site','id_collectivite','id_site');
    }
    public function withEnums(){
        $dep=$this->departement_siege()->first();
        $reg=$this->region_siege()->first();
        $nat=$this->nature_juridique()->first();
        $this->departement_siege=$dep?$dep->__toString():'';
        $this->region_siege=$reg?$reg->__toString():'';
        $this->nature_juridique=$nat?$nat->__toString():'';
    }
    public function updated_by(){
        return $this->hasOne(Admin::class, 'id_admin', 'updated_by');
    }
    public function effectif_history(){
        return InfoClientHistory::with('updated_by')->where('referenced_table', 'Epic')->where('id_reference', $this->id_epic)->orderBy('date_reference', 'DESC');
    }
    public function files(){
        return GEDFile::with('category')->where('type', 'epics')->where('entity_id', $this->id_epic);
    }

    public static function sync_api($token, $epics_sirens){

        $q = [];

        foreach($epics_sirens as $siren){
            $q[] = "siren:".str_replace(' ', '', $siren);
        }

        $departements = Departement::with('region')->get();        
        $deps = [];

        foreach($departements as $dep){
            $deps[strlen($dep->departement_code) == 2 ? $dep->departement_code : '0'.$dep->departement_code] = $dep;
        }

        $entities = ToolHelper::fetchDataFromInseeAPI($token, $q, function($entity) use ($deps){    
            
            $mapping = [];
            
            $dep_code = substr($entity['adresseEtablissement']['codePostalEtablissement'], 0, 2);
            if(isset($deps[$dep_code])){
                $dep = $deps[$dep_code];
                $mapping['departement_siege'] = $dep->id_departement;
                if($dep->region){
                    $mapping['region_siege'] = $dep->region->id_region;
                }else{
                    $mapping['region_siege'] = null;
                }
            }

            $mapping['nomEpic'] = $entity['uniteLegale']['denominationUniteLegale'];
            $mapping['nom_court'] = $entity['uniteLegale']['sigleUniteLegale'];

            $nature = Enemuration::where('code', $entity['uniteLegale']['categorieJuridiqueUniteLegale'])->first();


            if($nature){
                $mapping['nature_juridique'] = $nature->id_enemuration;
            }else{
                $mapping['nature_juridique'] = null;
            }

            return $mapping;
        });
        foreach($entities as $epic){
            EPIC::where('serin', $epic['serin'])->update($epic);
        }
        
        return true;
    }

}