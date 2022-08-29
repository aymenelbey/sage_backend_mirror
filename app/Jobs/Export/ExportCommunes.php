<?php

namespace App\Jobs\Export;

use App\Models\Commune;
use App\Http\Helpers\ExportHelper;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;

class ExportCommunes extends ExportDefault
{
    public function job($writer)
    {
        $status_values = ["VALIDATED" => "Validée / publiable", "NOT_VALIDATED" => "Non validée mais publiable", "NOT_PUBLISHED" => "Non publiable"];
        $structure = [
            "nomCommune" => "value",
            "serin" => "value",
            "siret" => "value",
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
            "adresse" => "value",
            "city" => "value",
            "country" => "value",
            "postcode" => "value",
            "insee" => "value",
            "nombreHabitant" => "value",
            "status" => [
                "type" => "map",
                "values" => $status_values
            ],
            "epic" => [
                "type" => "child",
                "structure" => [
                    "serin" => "value",
                    "nomEpic" => "value",
                    "adresse" => "value"
                ],
                "prefix" => "EPCI de rattachement - "
            ]
        ];
        $mapping = [
            "serin" => "Siren",
            "adresse" => "Adresse",
            "nomCommune" => "Nom Commune",
            "siret" => "Siret",
            "insee" => "INSEE",
            "city" => "Ville",
            "country" => "Pays",
            "postcode" => "Code postal",
            "departement_code" => "Code",
            "name_departement" => "Nom",
            "region_code" => "Code",
            "name_region" => "Nom",
            "nombreHabitant" => "Nbr d'habitants",
            "status" => "Statut de la fiche",
            "nomEpic" => "Nom",
        ];

        $writer->addRow(WriterEntityFactory::createRowFromArray(ExportHelper::get_headings($structure, null, $mapping)));

        Commune::with("departement_siege", "region_siege", "epic")->chunk($this->chunks, function ($communes) use ($structure, $mapping, $writer) {
            $communes = $communes->toArray();
            $mapped = array_map(function ($commune) use ($structure, $mapping) {
                return ExportHelper::to_exportable_array($commune, $structure, null, $mapping);
            }, $communes);
            foreach ($mapped as $row) $writer->addRow(WriterEntityFactory::createRowFromArray($row));
        });
    }
}
