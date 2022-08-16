<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\Site;
use App\Models\Enemuration;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;

class SitesPerCategorySheet extends StringValueBinder implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithCustomValueBinder
{
    private $category;
    private $data;

    private $status_display = [
        "VALIDATED" => "Validée / publiable",
        "NOT_VALIDATED" => "Non validée mais publiable",
        "NOT_PUBLISHED" => "Non publiable"
    ];

    private $persons_data = [
        "syndicat"=>[
            "typePersonMoral"=>"Syndicat",
            "name"=>"Nom Court",
            "dataIndex"=>"nomCourt"
        ],
        "epic"=>[
            "typePersonMoral"=>"Epic",
            "name"=>"Nom EPIC",
            "dataIndex"=>"nomEpic"
        ],
        "commune"=>[
            "typePersonMoral"=>"Commune",
            "name"=>"Nom Commune",
            "dataIndex"=>"nomCommune"
        ],
        "societe"=>[
            "typePersonMoral"=>"Societe",
            "name"=>"Groupe",
            "dataIndex"=>"groupe"
        ]
    ];

    private $valorisation_types = [
        'electric' => 'Electrique',
        'thermique' => 'Thermique',
        'hydrogene' => 'Hydrogene'
    ];

    public function __construct($category)
    {
        $this->category = $category;
        $this->data = Site::with("departement_siege", "region_siege", "dataTech")
            ->where('categorieSite', $category)->get()->map($this->map_site());
    }

    private function get_enum_value($id) {
        $enum = Enemuration::find($id);
        if ($enum) return $enum->value_enum;
        return "";
    }

    private function get_enum_array_display($ids) {
        if (!$ids) return "";
        if (!is_array($ids)) $ids = [$ids];
        $names = implode(" - ", array_unique(array_filter((array_map('self::get_enum_value', $ids)))));
        return empty($names) ? "" : $names;
    }

    private function map_site() : callable {
        return function ($site) {
            $general_infos = $this->general_infos($site);

            $data_tech = $site->dataTech->dataTech;
            if ($data_tech) {
                switch ($site->categorieSite) {
                    case "UVE":
                        $general_infos += $this->technical_infos_uve($data_tech);
                        break;
                    case "TRI":
                        $general_infos += $this->technical_infos_tri($data_tech);
                        break;
                    case "TMB":
                        $general_infos += $this->technical_infos_tmb($data_tech);
                        break;
                    case "ISDND":
                        $general_infos += $this->technical_infos_isdnd($data_tech);
                        break;
                }
            }
    
            
            return $general_infos;
        };
    }
    
    private function general_infos($site) {
        $array = $site->toArray();
        $departement_siege = isset($array["departement_siege"]) ? $array["departement_siege"]["name_departement"] : "";
        $region_siege = isset($array["region_siege"]) ? $array["region_siege"]["name_region"] : "";

        $client = $site->client;
        if ($client && $client->client) {
            $data = $this->persons_data[strtolower($client->typeCollectivite)];
            $type = $data["typePersonMoral"];
            $title = $data["name"];
            $city = $client->client->city;
            $name = $client->client[$data["dataIndex"]];
            $collectivite = "($type) $title: $name, Ville: $city";
        } else $collectivite = "";

        $exploitant = $site->exploitant;
        if ($exploitant && $exploitant->client) {
            $data = $this->persons_data[strtolower($exploitant->typeExploitant)];
            $societe = "";
            $name = $exploitant->client[$data["dataIndex"]];
            if (strtolower($exploitant->typeExploitant) == "societe" && $exploitant->client->groupe) {
                $groupes = json_decode($exploitant->client->groupe);
                $name = $this->get_enum_array_display($groupes);
            }
            if (!empty($name)) $societe .= " " . $data["name"] . ": $name";
            $societe_name = $exploitant->client->denomination;
            if (!empty($societe_name)) empty($societe) ? $societe .= " Société: $societe_name" : $societe .= ", Société: $societe_name";
            $city = $exploitant->client->city;
            if (!empty($city)) $societe .= ", Ville: $city";
            $societe = "(" . $data["typePersonMoral"] . ")" . $societe;
        } else $societe = "";

        $gestionnaire = $site->gestionnaire;
        $employe = $gestionnaire ? "Nom complet: $gestionnaire->nom $gestionnaire->prenom, Email: $gestionnaire->email, Status: " . ($gestionnaire->status ? "Actif" : "Inactif") : "";

        return [
            "#" => $site->id_site,
            "Dénomination" => $site->denomination,
            "Catégorie site" => $site->categorieSite,
            "Sinoe" => $site->sinoe,
            "Mode de gestion" => $site->modeGestion,
            "Département du siège" => $departement_siege,
            "Région du siège" => $region_siege,
            "Adresse" => $site->adresse,
            "Latitude" => $site->latitude,
            "Longitude" => $site->langititude,
            "Code postal" => $site->postcode,
            "Ville" => $site->city,
            "Pays" => $site->country,
            "Périodicité de relance" => $site->perdiocitRelance,
            "Année création" => $site->anneeCreation,
            "Site Internet" => $site->siteIntrnet,
            "Tél standard" => $site->telephoneStandrad,
            "Statut de la fiche" => $site->status && array_key_exists($site->status, $this->status_display) ? $this->status_display[$site->status] : "",
            "Collectivité publique" => $collectivite,
            "Société d'exploitation" => $societe,
            "Employé en charge" => $employe,
        ];
    }

    private function technical_infos_uve($data_tech) {
        $infos = $data_tech->infos;
        $enums = $data_tech->withEnums();
        $lines = $data_tech->lines;
        $valorisations = $data_tech->valorisations;
        return [
            "Type de dechets recus" => isset($enums["infos"]["typeDechetRecus"]) ? implode(", ",$enums["infos"]["typeDechetRecus"]) : "",
            "Installations complémentaires" => isset($enums["infos"]["installationComplementair"]) ? implode(", ",$enums["infos"]["installationComplementair"]) : "",
            "Capacité (t/h)" => isset($infos) ? $infos["capacite"] : "",
            "Tonnage réglementaire indiqué dans l'AP" => isset($infos) ? $infos["tonnageReglementaireAp"] : "",
            "Vide de four" => isset($infos) ? $infos["videFour"] : "",
            "Lignes d'incinération" => isset($lines) && count($lines) ? implode("\n", array_map(function ($line, $key) {
                $index = $key + 1;
                $capacite = $line["capacite"];
                $pci = $line["pci"];
                $type_fours = isset($line["typeFours"]) && count($line["typeFours"]) ? implode(" - ", array_map('self::get_enum_value', $line["typeFours"])) : "";
                $constructeur_four = $line["constructeurInstallation"] ? $this->get_enum_value($line["constructeurInstallation"]) : "";
                $type_chaudier = ""; // TODO: how to get it ?
                $constructeur_chaudiere = $line["constructeurChaudiere"] ? $this->get_enum_value($line["constructeurChaudiere"]) : "";
                $debit = $line["debitVapeur"];
                $pression = $line["cycleVapeurPression"];
                $temp = $line["cycleVapeurTemp"];
                $fumees = isset($line["traitementFumee"]) && count($line["traitementFumee"]) ? implode(" - ", array_map('self::get_enum_value', $line["traitementFumee"])) : "";
                $equipeProcessTF = isset($line["equipeProcessTF"]) && count($line["equipeProcessTF"]) ? implode(" - ", array_map('self::get_enum_value', $line["equipeProcessTF"])) : "";
                $reactif = isset($line["reactif"]) && count($line["reactif"]) ? implode(" - ", array_map('self::get_enum_value', $line["reactif"])) : "";
                $traitementNOX = isset($line["traitementNOX"]) && count($line["traitementNOX"]) ? implode(" - ", array_map('self::get_enum_value', $line["traitementNOX"])) : "";
                $reactifNOX = isset($line["reactifNOX"]) && count($line["reactifNOX"]) ? implode(" - ", array_map('self::get_enum_value', $line["reactifNOX"])) : "";
                $installationComplementair = isset($line["installationComplementair"]) && count($line["installationComplementair"]) ? implode(" - ", array_map('self::get_enum_value', $line["installationComplementair"])) : "";
                $commentTraitementFumee = $line["commentTraitementFumee"];
                $miseEnService = $line["miseEnService"];
                $revampingDate = $line["revampingDate"];
                $arretDate = $line["arretDate"];
                return "Ligne: $index, Capacité (t/h): $capacite, PCI (kcal/kg): $pci, Types de four: $type_fours, Constructeur four: $constructeur_four,  Constructeur Chaudière: $constructeur_chaudiere, Débit Vapeur (t/h): $debit, Condition vapeur sortie chaudière (Pression): $pression, Condition vapeur sortie chaudière (Température): $temp, Type traitement des fumées: $fumees, Equipement de process TF: $equipeProcessTF, Réactifs traitement de fumée: $reactif, Type traitement des oxydes d'azote (NOx): $traitementNOX, Réactifs DENOX: $reactifNOX, Installations complémentaires: $installationComplementair, Commentaire traitement fumée: $commentTraitementFumee, Date mise en service: $miseEnService, Date revamping: $revampingDate, Date arret ligne: $arretDate";
            }, $lines, array_keys($lines))) : "",
            "Types valorisations" => isset($enums["valorisations"]["valorisationTypes"]) ? implode(", ", array_map(function ($item) {
                    return array_key_exists($item, $this->valorisation_types) ? $this->valorisation_types[$item] : $item;
                }, $enums["valorisations"]["valorisationTypes"])) : "",
            "Performance Energétique (Pe / R1)" => isset($enums["valorisations"]["performenceEnergetique"]) ? $enums["valorisations"]["performenceEnergetique"] : "",
            "Electricité vendue (MWh/a)" => isset($enums["valorisations"]["electriciteVendue"]) ? $enums["valorisations"]["electriciteVendue"] : "",
            "Chaleur vendue (MWh/an)" => isset($enums["valorisations"]["chaleurVendue"]) ? $enums["valorisations"]["chaleurVendue"] : "",
            "Quantité H2 vendue (t/an)" => isset($enums["valorisations"]["H2Vendue"]) ? $enums["valorisations"]["H2Vendue"] : "",
            "Informations complémentaires" => isset($enums["valorisations"]["informationComplementaire"]) ? $enums["valorisations"]["informationComplementaire"] : "",
            "Valorisations énergétique" => isset($valorisations["blocks"]) && count($valorisations["blocks"]) ? implode("\n", array_map(function ($valorisation, $key) {
                $index = $key + 1;
                $type = array_key_exists($valorisation["type"], $this->valorisation_types) ? $this->valorisation_types[$valorisation["type"]] : $valorisation["type"];
                $name = $valorisation["name"];
                $miseEnService = $valorisation["miseEnService"];
                $typeEquipement = $valorisation["typeEquipement"] ? $this->get_enum_value($valorisation["typeEquipement"]) : "";
                $marqueEquipement = $valorisation["marqueEquipement"] ? $this->get_enum_value($valorisation["marqueEquipement"]) : "";
                $puissanceInstallee = $valorisation["puissanceInstallee"];
                $electriciteVendue = $valorisation["electriciteVendue"];
                return "Valorisation: $index, Type: $type, Nom: $name, Date mise en service: $miseEnService, Type équipement: $typeEquipement, Marque équipement: $marqueEquipement, Puissance Installée (MW): $puissanceInstallee, Electricité vendue (MWh/a): $electriciteVendue";
            }, $valorisations["blocks"], array_keys($valorisations["blocks"]))) : "", 
        ];
    }

    private function technical_infos_tri($data_tech) {
        return [
            "Capacité horaire Tonnes/h" => $data_tech->capaciteHoraire,
            "Capacité nominale (T/an)" => $data_tech->capaciteNominale,
            "Capacité réglementaire" => $data_tech->capaciteReglementaire,
            "Dernier constructeur connu" => $data_tech->dernierConstructeur,
            "Date d'extension" => $data_tech->dateExtension,
            "Date mise en service" => $data_tech->miseEnService,
            "Extension" => $this->get_enum_value($data_tech->extension),
        ];
    }

    private function technical_infos_tmb($data_tech) {
        return [
            "Quantité de refus (t)" => $data_tech->quantiteRefus,
            "CSR produit (t)" => $data_tech->CSRProduit,
            "Envoi pour préparation CSR (t)" => $data_tech->envoiPreparation,
            "Tonnage annuel" => $data_tech->tonnageAnnuel,
            "Capacité nominale" => $data_tech->capaciteNominal,
            "Dernier constructeur connu" => $data_tech->dernierConstruct,
            "Type d'installations" => $this->get_enum_value($data_tech->typeInstallation),
            "Types de déchets acceptés" => $this->get_enum_array_display($data_tech->typeDechetAccepter),
            "Technologie" => $this->get_enum_array_display($data_tech->technologie),
            "Valoristaion energétique" => $this->get_enum_array_display($data_tech->valorisationEnergitique),
            "Autres activite du site" => $this->get_enum_array_display($data_tech->autreActivite),
        ];
    }

    private function technical_infos_isdnd($data_tech) {
        return [
            "Capacité nominale (T/an)" => $data_tech->capaciteNominale,
            "Capacité restante" => $data_tech->capaciteRestante,
            "Capacité réglementaire" => $data_tech->capaciteReglementaire,
            "Projet d'extension ?" => $data_tech->projetExtension ? "Oui" : "Non",
            "Date d'extension" => $data_tech->dateExtension,
            "Date d'ouverture" => $data_tech->dateOuverture,
            "Date de fermeture" => $data_tech->dateFermeture,
            "Date de fermeture prévisionnelle" => $data_tech->dateFermeturePrev,
        ];
    }

    public function collection()
    {
        return $this->data;
    }

    public function title(): string
    {
        return $this->category;
    }

    public function headings(): array {
        if ($this->data) return array_keys($this->data->first());
        return [];
    }
}



?>