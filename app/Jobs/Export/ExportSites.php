<?php

namespace App\Jobs\Export;

use App\Models\Site;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use App\Http\Helpers\ExportHelper;
use App\Models\DataTechnUVE;

class ExportSites extends ExportDefault
{
    protected $category;

    public function __construct($user, $title, $failed_action, $category)
    {
        parent::__construct($user, $title, $failed_action);
        $this->category = $category;
    }

    public function job($writer)
    {
        $tech_structure = [];
        $mapping = [];
        switch ($this->category) {
            case "UVE":
                
                $lines_count = DataTechnUVE::selectRaw("id_data_uve, json_array_length(lines) as lines_count")
                ->whereNotNull("lines")
                ->groupBy("id_data_uve")
                ->orderByRaw("lines_count DESC")
                ->value("lines_count");

                if (!isset($lines_count)) $lines_count = 1;

                $valorisations_count = DataTechnUVE::selectRaw("id_data_uve, json_array_length(valorisations -> 'blocks') as valorisations_count")
                    ->whereRaw("valorisations -> 'blocks' IS NOT NULL")
                    ->groupBy("id_data_uve")
                    ->orderByRaw("valorisations_count DESC")
                    ->value("valorisations_count");

                if (!isset($valorisations_count)) $valorisations_count = 1;

                $valorisation_types = ["electric" => "Electrique", "thermique" => "Thermique", "hydrogene" => "Hydrogene"];

                $tech_structure = [
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
                        "installationComplementair" => "Installations compl??mentaires",
                        "capacite" => "Capacit?? (t/h)",
                        "tonnageReglementaireAp" => "Tonnage r??glementaire indiqu?? dans l'AP",
                        "videFour" => "Vide de four",
                        "valorisationTypes" => "Types valorisation",
                        "agregateurElectrique" => "Agr??gateur - acheteur ??lectricit??",
                        "performenceEnergetique" => "Performance Energ??tique (Pe / R1)",
                        "electriciteVendue" => "Electricit?? vendue (MWh/a)",
                        "chaleurVendue" => "Chaleur vendue (MWh/an)",
                        "H2Vendue" => "Quantit?? H2 vendue (t/an)",
                        "informationComplementaire" => "Informations compl??mentaires",
                    ]; 

                break;
            case "TRI":
                $tech_structure = [
                    "capaciteHoraire" => "value",
                    "capaciteNominale" => "value",
                    "capaciteReglementaire" => "value",
                    "dernierConstructeur" => "value",
                    "dateExtension" => "value",
                    "miseEnService" => "value",
                    "extension" => "enum",
                ];
        
                $mapping += [
                    "capaciteHoraire" => "Capacit?? horaire Tonnes/h",
                    "capaciteNominale" => "Capacit?? nominale (T/an)",
                    "capaciteReglementaire" => "Capacit?? r??glementaire",
                    "dernierConstructeur" => "Dernier constructeur connu",
                    "dateExtension" => "Date d'extension",
                    "miseEnService" => "Date mise en service",
                    "extension" => "Extension",
                ];
                break;
            case "TMB":
                $tech_structure = [
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

                $mapping += [
                    "quantiteRefus" => "Quantit?? de refus (t)",
                    "CSRProduit" => "CSR produit (t)",
                    "envoiPreparation" => "Envoi pour pr??paration CSR (t)",
                    "tonnageAnnuel" => "Tonnage annuel",
                    "capaciteNominal" => "Capacit?? nominale",
                    "dernierConstruct" => "Dernier constructeur connu",
                    "typeInstallation" => "Type d'installation",
                    "typeDechetAccepter" => "Types de d??chets accept??s",
                    "technologie" => "Technologies",
                    "valorisationEnergitique" => "Valorisations energ??tique",
                    "autreActivite" => "Autres activit??s du site",
                ];
                break;
            case "ISDND":
                $yes_no_values = ["Non", "Oui"];

                $tech_structure = [
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

                $mapping += [
                    "capaciteNominale" => "Capacit?? nominale (T/an)",
                    "capaciteRestante" => "Capacit?? restante",
                    "capaciteReglementaire" => "Capacit?? r??glementaire",
                    "projetExtension" => "Projet d'extension ?",
                    "dateExtension" => "Date d'extension",
                    "dateOuverture" => "Date d'ouverture",
                    "dateFermeture" => "Date de fermeture",
                    "dateFermeturePrev" => "Date de fermeture pr??visionnelle",
                ];
                break;
        }
        
        $status_values = ["VALIDATED" => "Valid??e / publiable", "NOT_VALIDATED" => "Non valid??e mais publiable", "NOT_PUBLISHED" => "Non publiable"];
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
                "prefix" => "D??partement - "
            ],
            "region_siege" => [
                "type" => "child",
                "structure" => [
                    "region_code" => "value",
                    "name_region" => "value",
                ],
                "prefix" => "R??gion - "
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
                "prefix" => "Collectivit?? - "
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
                "prefix" => "Societ?? - "
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
                "prefix" => "Employ?? - ",
                "mapping" => [
                    "nom" => "Nom",
                    "prenom" => "Pr??nom",
                    "email" => "Email",
                    "status" => "Status"
                ]
            ],
            "data_tech" => [
                "type" => "child",
                "structure" => [
                    "data_tech" => [
                        "type" => "child",
                        "structure" => $tech_structure
                    ]
                ]
            ]

        ];
        
        $mapping += [
            "denomination" => "Nom",
            "categorieSite" => "Cat??gorie site",
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
            "perdiocitRelance" => "P??riodicit?? de relance",
            "anneeCreation" => "Ann??e cr??ation",
            "siteIntrnet" => "Site internet",
            "telephoneStandrad" => "T??l standard",
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

        $writer->addRow(WriterEntityFactory::createRowFromArray(ExportHelper::get_headings($structure, null, $mapping)));
        
        Site::where("categorieSite", $this->category)->with("departement_siege", "region_siege", "client.client", "exploitant.client", "gestionnaire", "dataTech.dataTech")->chunk($this->chunks, function ($sites) use ($structure, $mapping, $writer) {
            $sites = $sites->toArray();
            $mapped = array_map(function ($site) use ($structure, $mapping) {
                return ExportHelper::to_exportable_array($site, $structure, null, $mapping);
            }, $sites);
            foreach ($mapped as $row) $writer->addRow(WriterEntityFactory::createRowFromArray($row));
        });
    }
}
