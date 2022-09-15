<?php

namespace App\Jobs\Export;

use App\Models\Contact;
use App\Models\ContactHasPersonMoral;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use App\Http\Helpers\ExportHelper;
use Illuminate\Support\Arr;

class ExportContacts extends ExportDefault
{
    public function job($writer)
    {
        $persons_count = ContactHasPersonMoral::selectRaw("id_contact, count(*) as count")
            ->groupBy("id_contact")
            ->orderByRaw("count DESC")
            ->value("count");

        $status_values = ["Inactif", "Actif"];

        $structure = [
            "genre" => "value",
            "status" => [
                "type" => "map",
                "values" => $status_values
            ],
            "nom" => "value",
            "prenom" => "value",
            "telephone" => "value",
            "mobile" => "value",
            "email" => "value",
            "address" => "value",
            "informations" => "value",
            "linkedin" => "value",
            "persons_moral" => [
                "type" => "list",
                "structure" => [
                    "typePersonMoral" => "value",
                    "person" => [
                        "type" => "child",
                        "structure" => [
                            "serin" => "value",
                            "dataIndex" => "ref",
                            "denomination" => "value",
                            "groupe" => "enum_array",
                            "city" => "value"
                        ],
                        "mapping" => [
                            "denomination" => "nom",
                            "dataIndex" => "nom",
                            "nomEpic" => "nom",
                            "nomCourt" => "nom",
                            "nomCommune" => "nom",
                            "serin" => "siren",
                            "city" => "ville"
                        ]
                    ],
                    "fonctions" => "enum_array"
                ],
                "prefix" => "person",
                "mapping" => [
                    "typePersonMoral" => "type"
                ],
                "count" => $persons_count
            ]
        ];

        $mapping = [
            "genre" => "Civilité",
            "status" => "Statut",
            "nom" => "Nom",
            "prenom" => "Prénom",
            "telephone" => "Téléphone",
            "mobile" => "Mobile",
            "email" => "Email",
            "address" => "Adresse",
            "informations" => "Informations",
            "linkedin" => "LinkedIn",
        ];

        $writer->addRow(WriterEntityFactory::createRowFromArray(ExportHelper::get_headings($structure, null, $mapping)));
        
        Contact::with("persons_moral")->chunk($this->chunks, function ($contacts) use ($structure, $mapping, $writer) {
            $contacts = $contacts->toArray();
            $mapped = array_map(function ($contact) use ($structure, $mapping) {
                $contact["persons_moral"] = array_map(function ($person) {
                    $person["fonctions"] = Arr::pluck($person["fonction_person"], "functionPerson");
                    return $person;
                }, $contact["persons_moral"]);
                return ExportHelper::to_exportable_array($contact, $structure, null, $mapping);
            }, $contacts);
            foreach ($mapped as $row) $writer->addRow(WriterEntityFactory::createRowFromArray($row));
        });
    }
}
