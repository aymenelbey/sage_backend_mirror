<?php

namespace App\Jobs\Export;

use App\Models\SocieteExploitant;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use App\Http\Helpers\ExportHelper;

class ExportSocietes extends ExportDefault
{
    public function job($writer)
    {
        $status_values = ["VALIDATED" => "Validée / publiable", "NOT_VALIDATED" => "Non validée mais publiable", "NOT_PUBLISHED" => "Non publiable"];

        $structure = [
            "denomination" => "value",
            "groupe" => "enum_array",
            "adresse" => "value",
            "city" => "value",
            "country" => "value",
            "postcode" => "value",
            "serin" => "value",
            "siret" => "value",
            "sinoe" => "value",
            "nature_juridique" => "enum",
            "codeape" => "enum",
            "siteInternet" => "value",
            "telephoneStandrad" => "value",
            "effectifs" => "value",
            "status" => [
                "type" => "map",
                "values" => $status_values
            ]
        ];

        $mapping = [
            "serin" => "Siren",
            "adresse" => "Adresse",
            "denomination" => "Dénomination",
            "siret" => "Siret",
            "sinoe" => "Sinoe",
            "city" => "Ville",
            "country" => "Pays",
            "postcode" => "Code postal",
            "siteInternet" => "Site Internet",
            "nature_juridique" => "Nature juridique",
            "telephoneStandrad" => "Tél standard",
            "effectifs" => "Effectifs",
            "status" => "Statut de la fiche",
            "codeape" => "Code Ape",
            "groupe" => "Groupe"
        ];

        $writer->addRow(WriterEntityFactory::createRowFromArray(ExportHelper::get_headings($structure, null, $mapping)));
        
        SocieteExploitant::chunk($this->chunks, function ($syndicats) use ($structure, $mapping, $writer) {
            $syndicats = $syndicats->toArray();
            $mapped = array_map(function ($syndicat) use ($structure, $mapping) {
                return ExportHelper::to_exportable_array($syndicat, $structure, null, $mapping);
            }, $syndicats);
            foreach ($mapped as $row) $writer->addRow(WriterEntityFactory::createRowFromArray($row));
        });
    }
}
