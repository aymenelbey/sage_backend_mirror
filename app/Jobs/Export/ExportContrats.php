<?php

namespace App\Jobs\Export;

use App\Models\Contrat;
use App\Models\CommunHasContrat;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use App\Http\Helpers\ExportHelper;

class ExportContrats extends ExportDefault
{
    public function job($writer)
    {
        $count_acteurs = CommunHasContrat::selectRaw("id_contrat, count(*) as count")
            ->groupBy("id_contrat")
            ->orderByRaw("count DESC")
            ->value("count");

        $structure = [
            "site" => [
                "type" => "child",
                "structure" => [
                    "sinoe" => "value",
                    "denomination" => "value",
                    "categorieSite" => "value",
                    "modeGestion" => "value",
                    "city" => "value"
                ],
                "prefix" => "Site - "
            ],
            "communes" => [
                "type" => "list",
                "structure" => [
                    "typePersonMoral" => "value",
                    "siren" => "value",
                    "dataIndex" => "ref",
                    "city" => "value"
                ],
                "prefix" => "acteurs",
                "mapping" => [
                    "typePersonMoral" => "type",
                    "nomEpic" => "nom",
                    "nomCourt" => "nom",
                    "nomCommune" => "nom",
                    "dataIndex" => "nom"
                ],
                "prefix" => "acteur",
                "count" => $count_acteurs
            ],
            "contractant" => [
                "type" => "child",
                "structure" => [
                    "sinoe" => "value",
                    "groupe" => "enum_array",
                    "denomination" => "value"
                ],
                "prefix" => "Contractant - "
            ],
            "dateDebut" => "value",
            "dateFin" => "value"
        ];

        $mapping = [
            "sinoe" => "Sinoe",
            "denomination" => "Dénomination",
            "categorieSite" => "Catégorie",
            "modeGestion" => "Mode de gestion",
            "city" => "Ville",
            "serin" => "Siren",
            "groupe" => "Groupe",
            "dateDebut" => "Début du Contrat",
            "dateFin" => "Fin du Contrat"
        ];

        $writer->addRow(WriterEntityFactory::createRowFromArray(ExportHelper::get_headings($structure, null, $mapping)));
        
        Contrat::with("site", "communes", "contractant")->chunk($this->chunks, function ($contrats) use ($structure, $mapping, $writer) {
            $contrats = $contrats->toArray();
            $mapped = array_map(function ($contrat) use ($structure, $mapping) {
                return ExportHelper::to_exportable_array($contrat, $structure, null, $mapping);
            }, $contrats);
            foreach ($mapped as $row) $writer->addRow(WriterEntityFactory::createRowFromArray($row));
        });
    }
}
