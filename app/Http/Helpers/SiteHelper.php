<?php

namespace App\Http\Helpers;
use Validator;
use Carbon\Carbon;
class SiteHelper
{
    protected static $RULES_CREATE=[
        "categorieSite"=>["required",'in:UVE,TRI,TMB,ISDND'],
        "sinoe"=>["required"],
        "modeGestion"=>["required"],
        "denomination"=>["required"],
        "adresse"=>['required'],
        "latitude"=>["required"],
        "langititude"=>["required"],
        "gestionaire"=>["required","exists:gestionnaires,id_gestionnaire"],
        "client"=>["required","exists:collectivites,id_collectivite"],
        "typeExploitant"=>['required','in:Syndicat,Epic,Commune,Societe'],
        'departement_siege'=>["required","exists:enemurations,id_enemuration"],
        'region_siege'=>["required","exists:enemurations,id_enemuration"],
        "societe"=>['required']
    ];
    public static function validateSiteInfo($dataEntry){
        if(!empty($dataEntry["typeExploitant"])){
            switch($dataEntry["typeExploitant"]){
                case "Epic":
                    self::$RULES_CREATE["societe"]=['required',"exists:epics,id_epic"];
                    break;
                case "Syndicat":
                    self::$RULES_CREATE["societe"]=['required',"exists:syndicats,id_syndicat"];
                    break;
                case "Commune":
                    self::$RULES_CREATE["societe"]=['required',"exists:communes,id_commune"];
                    break;
                case "Societe":
                    self::$RULES_CREATE["societe"]=['required',"exists:societe_exploitants,id_societe_exploitant"];
                    break;
            }
        }
        $validator = Validator::make($dataEntry,self::$RULES_CREATE);
        return $validator;
    }
    public static function extractTechData($techData,String $typeSite){
        $techReturn=[];
        switch($typeSite){
            case "UVE":
                $techReturn=$techData->only(["typeDechetRecus",'nombreFours',"capacite","nombreChaudiere","debitEau","miseEnService","typeFoursChaudiere","traitementFumee","installationComplementair","capaciteMaxAnu","videFour","voiTraiFemuee","traitementNOX","reseauChaleur","rsCommentaire","tonnageReglementaireAp","equipeProcessTF","reactif","performenceEnergetique","cycleVapeur","typeTerboalternateur","terboalternateur","venteProduction","constructeurInstallation"])->toArray();
                break;
            case "TRI":
                $techReturn=$techData->only(["capaciteHoraire","capaciteNominale","capaciteReglementaire","extension","dateExtension","miseEnService","dernierConstructeur"])->toArray();
                break;
            case "TMB":
                $techReturn=$techData->only(["typeInstallation","typeDechetAccepter","technologie","quantiteRefus","CSRProduit","envoiPreparation","tonnageAnnuel","capaciteNominal","autreActivite","dernierConstruct","valorisationEnergitique"])->toArray();
                break;
            case "ISDND":
                $techReturn=$techData->only(["capaciteNominale","capaciteRestante","capaciteReglementaire","projetExtension","dateExtension","dateOuverture","dateFermeture","dateFermeturePrev"])->toArray();
                break;
        }
        return $techReturn;
    }
    public static function extractSiteData($siteinfo){
        $infoUse=$siteinfo->only(["denomination","categorieSite","adresse","latitude","langititude","siteIntrnet","telephoneStandrad","anneeCreation","photoSite","modeGestion","perdiocitRelance","sinoe","departement_siege","region_siege"])->toArray();
        return $infoUse;
    }
    public static function formatDateIfNotNull($date){
        return isset($date) && !empty($date) ? Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d') : NULL;
    }
    public static function prepareCols($type, $cols, $imploded = false){
        $result = [];
        if($type == 'Site'){
            foreach($cols as $key => $value){
                if(is_array($value)){
                    $result = array_merge($result, array_keys(array_filter($value, function($v, $k){
                        return $v;
                    }, ARRAY_FILTER_USE_BOTH))); 
                }else if($value){
                    $result[] = $key;
                }
            }
            if($imploded) return implode("|", $result);
        }else{
            foreach($cols as $col => $values){
                $result[] = implode("$", array_merge([$col], array_keys(array_filter($values, function($v, $k){
                    return $v;
                }, ARRAY_FILTER_USE_BOTH))));
            }
            if($imploded) return implode("&", $result);
        }
        
        return $result;
    }
    public static function explodeCols($type, $cols){
        $columns = [];
        if($type == "Site"){
            foreach(explode("|", $cols) as $col){
                $columns[$col] = true;
            }    
        }else{
            $clmns= explode("&",$cols);
            foreach($clmns as $clm){
                $tmp = explode("$", $clm);
                if(count($tmp) >= 2){
                    $columns[$tmp[0]]=[];
                    foreach(array_slice($tmp, 1) as $retr){
                        $columns[$tmp[0]][$retr]=true; 
                    }
                }
            } 
        }
        return $columns;
    }
}