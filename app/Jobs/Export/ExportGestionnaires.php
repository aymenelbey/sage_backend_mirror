<?php

namespace App\Jobs\Export;

use App\Models\Gestionnaire;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use App\Http\Helpers\ExportHelper;

class ExportGestionnaires extends ExportDefault
{
    public function job($writer)
    {
        $status_values = ["Inactif", "Actif"];
        $societe_values = [
            "Sage_engineering" => "SAGE ENGINEERING",
            "Sage_expert" => "SAGE EXPERT",
            "Sage_industry" => "SAGE INDUSTRY"
        ];

        $structure = [
            "genre" => "value",
            "status" => [
                "type" => "map",
                "values" => $status_values
            ],
            "nom" => "value",
            "prenom" => "value",
            "mobile" => "value",
            "telephone" => "value",
            "email" => "value",
            "societe" => [
                "type" => "map",
                "values" => $societe_values
            ],
        ];

        $mapping = [
            "genre" => "Civilité",
            "status" => "Statut",
            "nom" => "Nom",
            "prenom" => "Prénom",
            "mobile" => "Mobile",
            "telephone" => "Téléphone",
            "email" => "Email",
            "societe" => "Société",
        ];

        $writer->addRow(WriterEntityFactory::createRowFromArray(ExportHelper::get_headings($structure, null, $mapping)));
        

        Gestionnaire::chunk($this->chunks, function ($gestionnaires) use ($structure, $mapping, $writer) {
            $gestionnaires = $gestionnaires->toArray();
            $mapped = array_map(function ($gestionnaire) use ($structure, $mapping) {
                return ExportHelper::to_exportable_array($gestionnaire, $structure, null, $mapping);
            }, $gestionnaires);
            foreach ($mapped as $row) $writer->addRow(WriterEntityFactory::createRowFromArray($row));
        });
    }
}
