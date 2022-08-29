<?php

namespace App\Jobs\Export;

use App\Models\Syndicat;
use App\Models\CompetanceDechet;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use App\Http\Helpers\ExportHelper;

class ExportSyndicats extends ExportDefault
{
    public function job($writer)
    {
        $comp_ex_count = CompetanceDechet::selectRaw("owner_competance, count(*) as count")
            ->where("owner_type", "Syndicat")
            ->whereNull("delegue_competance")
            ->groupBy("owner_competance")
            ->orderByRaw("count DESC")
            ->value("count");

        $comp_dg_count = CompetanceDechet::selectRaw("owner_competance, count(*) as count")
            ->where("owner_type", "Syndicat")
            ->whereNotNull("delegue_competance")
            ->groupBy("owner_competance")
            ->orderByRaw("count DESC")
            ->value("count");

        Syndicat::withCount("competance_exercee", "competance_delegue")->chunk($this->chunks, function ($syndicats) use (&$comp_ex_count, &$comp_dg_count) {
            $max = $syndicats->max("competance_exercee_count");
            if ($max > $comp_ex_count) $comp_ex_count = $max;
            $max = $syndicats->max("competance_delegue_count");
            if ($max > $comp_dg_count) $comp_dg_count = $max;
        });

        $status_values = ["VALIDATED" => "Validée / publiable", "NOT_VALIDATED" => "Non validée mais publiable", "NOT_PUBLISHED" => "Non publiable"];

        $structure = [
            "nomCourt" => "value",
            "denominationLegale" => "value",
            "sinoe" => "value",
            "serin" => "value",
            "siret" => "value",
            "adresse" => "value",
            "city" => "value",
            "country" => "value",
            "postcode" => "value",
            "email" => "value",
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
            "nature_juridique" => "enum",
            "amobe" => "enum",
            "siteInternet" => "value",
            "telephoneStandard" => "value",
            "nombreHabitant" => "value",
            "status" => [
                "type" => "map",
                "values" => $status_values
            ],
            "competance_exercee" => [
                "type" => "list",
                "structure" => [
                    "code" => "value",
                    "competence_dechet_name" => "value",
                    "start_date" => "value",
                    "end_date" => "value",
                    "comment" => "value",
                ],
                "prefix" => "comp_ex",
                "count" => $comp_ex_count
            ],
            "competance_delegue" => [
                "type" => "list",
                "structure" => [
                    "code" => "value",
                    "competence_dechet_name" => "value",
                    "delegue_type" => "value",
                    "delegue_competance" => [
                        "type" => "child",
                        "structure" => [
                            "serin" => "value",
                            "dataIndex" => "ref"
                        ],
                        "mapping" => [
                            "serin" => "delegue_siren",
                            "nomEpic" => "delegue_nom",
                            "nomCourt" => "delegue_nom",
                            "nomCommune" => "delegue_nom",
                            "dataIndex" => "delegue_nom"
                        ]
                    ],
                    "start_date" => "value",
                    "end_date" => "value",
                    "comment" => "value",
                ],
                "prefix" => "comp_dg",
                "count" => $comp_dg_count
            ],
        ];

        $mapping = [
            "serin" => "Siren",
            "adresse" => "Adresse",
            "denominationLegale" => "Dénomination légale",
            "nomCourt" => "Nom Court",
            "email" => "Email",
            "amobe" => "BE AMO",
            "siret" => "Siret",
            "sinoe" => "Sinoe",
            "city" => "Ville",
            "country" => "Pays",
            "postcode" => "Code postal",
            "siteInternet" => "Site Internet",
            "departement_code" => "Code",
            "name_departement" => "Nom",
            "region_code" => "Code",
            "name_region" => "Nom",
            "nature_juridique" => "Nature juridique",
            "telephoneStandard" => "Tél standard",
            "nombreHabitant" => "Nbr d'habitants",
            "status" => "Statut de la fiche",
            "code" => "Code",
            "competence_dechet_name" => "Compétence",
            "start_date" => "Date début",
            "end_date" => "Date fin",
            "comment" => "Commentaire",
            "delegue_type" => "Type délégué",
        ];

        $writer->addRow(WriterEntityFactory::createRowFromArray(ExportHelper::get_headings($structure, null, $mapping)));
        
        Syndicat::with("departement_siege", "region_siege", "competance_exercee", "competance_delegue")->chunk($this->chunks, function ($syndicats) use ($structure, $mapping, $writer) {
            $syndicats = $syndicats->toArray();
            $mapped = array_map(function ($syndicat) use ($structure, $mapping) {
                return ExportHelper::to_exportable_array($syndicat, $structure, null, $mapping);
            }, $syndicats);
            foreach ($mapped as $row) $writer->addRow(WriterEntityFactory::createRowFromArray($row));
        });
    }
}
