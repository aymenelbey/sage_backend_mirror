<?php

namespace App\Http\Helpers;
use Validator;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Models\Site;

class SiteHelper
{
    protected static $RULES_CREATE=[
        "categorieSite"=>["required",'in:UVE,TRI,TMB,ISDND'],
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
        "societe"=>['required'],
        "city" => ['required'],
        "country" => ['required'],
        "postcode" => ['required']
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
        $sinoe = [];
        
        if(isset($dataEntry['id_site'])){
            $sinoe = ['sinoe' => ["required", Rule::unique('sites', 'sinoe')->ignore($dataEntry["id_site"], 'id_site')]];
        }else{
            $sinoe = ['sinoe' => ["required", "unique:sites,sinoe"]];
        }

        $validator = Validator::make($dataEntry,array_merge(self::$RULES_CREATE, $sinoe));

        return $validator;
    }
    public static function extractTechData($techData,String $typeSite){
        $techReturn=[];
        switch($typeSite){
            case "UVE":
                // $techReturn=$techData->only(["typeDechetRecus",'nombreFours',"capacite","nombreChaudiere","debitEau","miseEnService","typeFoursChaudiere","traitementFumee","installationComplementair","capaciteMaxAnu","videFour","voiTraiFemuee","traitementNOX","reseauChaleur","rsCommentaire","tonnageReglementaireAp","equipeProcessTF","reactif","performenceEnergetique","cycleVapeur","typeTerboalternateur","terboalternateur","venteProduction","constructeurInstallation"])->toArray();
                $techReturn=$techData->only(['infos', 'lines', 'valorisations'])->toArray();
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
        $infoUse=$siteinfo->only(["denomination","categorieSite",'status',"adresse","latitude","langititude","city", "country", "postcode", "siteIntrnet","telephoneStandrad","anneeCreation","photoSite","modeGestion","perdiocitRelance","sinoe","departement_siege","region_siege"])->toArray();
        return $infoUse;
    }
    public static function formatDateIfNotNull($date){
        return isset($date) && !empty($date) ? Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d') : NULL;
    }
    public static function prepareCols($type, $cols, $imploded = false){
        return $cols;
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
        return $cols;
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
    
    public static function get_sites_export_data($category) {

        if (isset($category) && in_array($category, ["UVE", "TRI", "TMB", "ISDND"])) {

            $sites = Site::where("categorieSite", $category)->with("departement_siege", "region_siege", "client.client", "exploitant.client", "gestionnaire", "dataTech.dataTech")->get()->toArray();

            if ($category == "UVE") {

                $lines_count = ExportHelper::get_max_count($sites, "data_tech.data_tech.lines");
                $valorisations_count = ExportHelper::get_max_count($sites, "data_tech.data_tech.valorisations.blocks");

                return array_map(function ($site) use ($lines_count, $valorisations_count) {
                    $data = self::get_site_general_infos($site);
                    $data += self::get_site_technical_infos_uve($site["data_tech"]["data_tech"], $lines_count, $valorisations_count);
                    return $data;
                }, $sites);
            
            }

            return array_map(function ($site) use ($category) {
                $data = self::get_site_general_infos($site);
                if (isset($site["data_tech"]["data_tech"]))
                    $data += call_user_func("self::get_site_technical_infos_" . strtolower($category), $site["data_tech"]["data_tech"]);
                return $data;
            }, $sites);
        }
 
    }
    public static function get_site_general_infos($data) {

        $status_values = ["VALIDATED" => "Validée / publiable", "NOT_VALIDATED" => "Non validée mais publiable", "NOT_PUBLISHED" => "Non publiable"];
        $employee_status_values = ["Inactif", "Actif"];

        $structure = [
            "denomination" => "value",
            "categorieSite" => "value",
            "adresse" => "value",
            "latitude" => "value",
            "langititude" => "value",
            "siteIntrnet" => "value",
            "telephoneStandrad" => "value",
            "anneeCreation" => "value",
            "modeGestion" => "value",
            "perdiocitRelance" => "value",
            "sinoe" => "value",
            "status" => [
                "type" => "map",
                "values" => $status_values
            ],
            "city" => "value",
            "country" => "value",
            "postcode" => "value",
            "departement_siege" => [
                "type" => "child",
                "structure" => [
                    "departement_code" => "value",
                    "name_departement" => "value",
                ],
                "prefix" => "Département - "
            ],
            "region_siege" => [
                "type" => "child",
                "structure" => [
                    "region_code" => "value",
                    "name_region" => "value",
                ],
                "prefix" => "Région - "
            ],
            "client" => [
                "type" => "child",
                "structure" => [
                    "typeCollectivite" => "value",
                    "client" => [
                        "type" => "child",
                        "structure" => [
                            "serin" => "value",
                            "dataIndex" => "ref",
                            "denomination" => "value",
                            "groupe" => "enum_array",
                            "city" => "value"
                        ]
                    ]
                ],
                "prefix" => "Collectivité - "
            ],
            "exploitant" => [
                "type" => "child",
                "structure" => [
                    "typeExploitant" => "value",
                    "client" => [
                        "type" => "child",
                        "structure" => [
                            "serin" => "value",
                            "dataIndex" => "ref",
                            "denomination" => "value",
                            "groupe" => "enum_array",
                            "city" => "value"
                        ]
                    ]
                ],
                "prefix" => "Societé - "
            ],
            "gestionnaire" => [
                "type" => "child",
                "structure" => [
                    "email" => "value",
                    "nom" => "value",
                    "prenom" => "value",
                    "status" => [
                        "type" => "map",
                        "values" => $employee_status_values
                    ]
                ],
                "prefix" => "Employé - ",
                "mapping" => [
                    "nom" => "Nom",
                    "prenom" => "Prénom",
                    "email" => "Email",
                    "status" => "Status"
                ]
            ]
        ];
        
        $mapping = [
            "denomination" => "Nom",
            "categorieSite" => "Catégorie site",
            "sinoe" => "Sinoe",
            "modeGestion" => "Mode de gestion",
            "departement_code" => "Code",
            "name_departement" => "Nom",
            "region_code" => "Code",
            "name_region" => "Nom",
            "adresse" => "Adresse",
            "latitude" => "Latitude",
            "langititude" => "Longitude",
            "postcode" => "Code postal",
            "city" => "Ville",
            "country" => "Pays",
            "perdiocitRelance" => "Périodicité de relance",
            "anneeCreation" => "Année création",
            "siteIntrnet" => "Site internet",
            "telephoneStandrad" => "Tél standard",
            "status" => "Statut de la fiche",
            "typeCollectivite" => "Type",
            "typeExploitant" => "Type",
            "serin" => "Siren",
            "groupe" => "Groupe",
            "nomEpic" => "Nom",
            "nomCourt" => "Nom",
            "nomCommune" => "Nom",
            "dataIndex" => "Nom"
        ];

        return ExportHelper::to_exportable_array($data, $structure, null, $mapping);

    }
    public static function get_site_technical_infos_uve($data, $lines_count = null, $valorisations_count = null) {

        $valorisation_types = ["electric" => "Electrique", "thermique" => "Thermique", "hydrogene" => "Hydrogene"];

        $structure = [
            "infos" => [
                "type" => "child",
                "structure" => [
                    "typeDechetRecus" => "enum_array",
                    "installationComplementair" => "enum_array",
                    "capacite" => "value",
                    "tonnageReglementaireAp" => "value",
                    "videFour" => "value"
                ]
            ],
            "lines" => [
                "type" => "list",
                "structure" => [
                    "capacite" => "value",
                    "pci" => "value",
                    "typeFours" => "enum_array",
                    "constructeurInstallation" => "enum",
                    "typeChaudiere" => "enum_array",
                    "constructeurChaudiere" => "enum",
                    "debitVapeur" => "value",
                    "cycleVapeurPression" => "value",
                    "cycleVapeurTemp" => "value",
                    "traitementFumee" => "enum_array",
                    "equipeProcessTF" => "enum_array",
                    "reactif" => "enum_array",
                    "traitementNOX" => "enum_array",
                    "reactifNOX" => "enum_array",
                    "installationComplementair" => "enum_array",
                    "commentTraitementFumee" => "value",
                    "miseEnService" => "value",
                    "revampingDate" => "value",
                    "arretDate" => "value",
                ],
                "prefix" => "ligne",
                "count" => $lines_count
            ],
            "valorisations" => [
                "type" => "child",
                "structure" => [
                    "valorisationTypes" => [
                        "type" => "map_array",
                        "values" => $valorisation_types
                    ],
                    "agregateurElectrique" => "enum",
                    "performenceEnergetique" => "value",
                    "electriciteVendue" => "value",
                    "chaleurVendue" => "value",
                    "H2Vendue" => "value",
                    "informationComplementaire" => "value",
                    "blocks" => [
                        "type" => "list",
                        "structure" => [
                            "type" => [
                                "type" => "map",
                                "values" => $valorisation_types
                            ],
                            "name" => "value",
                            "miseEnService" => "value",
                            "typeEquipement" => "enum",
                            "marqueEquipement" => "enum",
                            "puissanceInstallee" => "value",
                            "electriciteVendue" => "value",
                            "RCUIndustirel" => "enum",
                            "client" => "enum_array",
                            "chaleurVendue" => "value",
                            "puissanceElectrolyseur" => "value",
                            "H2Vendue" => "value"
                        ],
                        "prefix" => "valorisation",
                        "count" => $valorisations_count
                    ]
                ]
            ]
        ];

        $mapping = [
            "typeDechetRecus" => "Types de dechets recus",
            "installationComplementair" => "Installations complémentaires",
            "capacite" => "Capacité (t/h)",
            "tonnageReglementaireAp" => "Tonnage réglementaire indiqué dans l'AP",
            "videFour" => "Vide de four",
            "valorisationTypes" => "Types valorisation",
            "agregateurElectrique" => "Agrégateur - acheteur électricité",
            "performenceEnergetique" => "Performance Energétique (Pe / R1)",
            "electriciteVendue" => "Electricité vendue (MWh/a)",
            "chaleurVendue" => "Chaleur vendue (MWh/an)",
            "H2Vendue" => "Quantité H2 vendue (t/an)",
            "informationComplementaire" => "Informations complémentaires",
        ]; 

        return ExportHelper::to_exportable_array($data, $structure, null, $mapping);

    }
    public static function get_site_technical_infos_tri($data) {
        
        $structure = [
            "capaciteHoraire" => "value",
            "capaciteNominale" => "value",
            "capaciteReglementaire" => "value",
            "dernierConstructeur" => "value",
            "dateExtension" => "value",
            "miseEnService" => "value",
            "extension" => "enum",
        ];

        $mapping = [
            "capaciteHoraire" => "Capacité horaire Tonnes/h",
            "capaciteNominale" => "Capacité nominale (T/an)",
            "capaciteReglementaire" => "Capacité réglementaire",
            "dernierConstructeur" => "Dernier constructeur connu",
            "dateExtension" => "Date d'extension",
            "miseEnService" => "Date mise en service",
            "extension" => "Extension",
        ];

        return ExportHelper::to_exportable_array($data, $structure, null, $mapping);

    }
    public static function get_site_technical_infos_tmb($data) {
        
        $structure = [
            "quantiteRefus" => "value",
            "CSRProduit" => "value",
            "envoiPreparation" => "value",
            "tonnageAnnuel" => "value",
            "capaciteNominal" => "value",
            "dernierConstruct" => "value",
            "typeInstallation" => "enum",
            "typeDechetAccepter" => "enum_array",
            "technologie" => "enum_array",
            "valorisationEnergitique" => "enum_array",
            "autreActivite" => "enum_array",
        ];

        $mapping = [
            "quantiteRefus" => "Quantité de refus (t)",
            "CSRProduit" => "CSR produit (t)",
            "envoiPreparation" => "Envoi pour préparation CSR (t)",
            "tonnageAnnuel" => "Tonnage annuel",
            "capaciteNominal" => "Capacité nominale",
            "dernierConstruct" => "Dernier constructeur connu",
            "typeInstallation" => "Type d'installation",
            "typeDechetAccepter" => "Types de déchets acceptés",
            "technologie" => "Technologies",
            "valorisationEnergitique" => "Valorisations energétique",
            "autreActivite" => "Autres activités du site",
        ];

        return ExportHelper::to_exportable_array($data, $structure, null, $mapping);

    }
    public static function get_site_technical_infos_isdnd($data) {

        $yes_no_values = ["Non", "Oui"];

        $structure = [
            "capaciteNominale" => "value",
            "capaciteRestante" => "value",
            "capaciteReglementaire" => "value",
            "projetExtension" => [
                "type" => "map",
                "values" => $yes_no_values
            ],
            "dateExtension" => "value",
            "dateOuverture" => "value",
            "dateFermeture" => "value",
            "dateFermeturePrev" => "value",
        ];

        $mapping = [
            "capaciteNominale" => "Capacité nominale (T/an)",
            "capaciteRestante" => "Capacité restante",
            "capaciteReglementaire" => "Capacité réglementaire",
            "projetExtension" => "Projet d'extension ?",
            "dateExtension" => "Date d'extension",
            "dateOuverture" => "Date d'ouverture",
            "dateFermeture" => "Date de fermeture",
            "dateFermeturePrev" => "Date de fermeture prévisionnelle",
        ];

        return ExportHelper::to_exportable_array($data, $structure, null, $mapping);

    }

}