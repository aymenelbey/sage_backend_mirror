<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMapping;
Use PhpOffice\PhpSpreadsheet\Shared\Date;


class CollectionsImport implements ToCollection,WithHeadingRow,WithMapping
{
    public function map($row): array
    {
        $newRow=[];
        foreach($row as $key=>$cell){
            if($cell){
                if(str_contains($key, 'date')){
                    $newRow[$key] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((int) $cell)->format('Y-m-d');
                }else{
                    $newRow[$key]=trim($cell);
                }
            }else{
                $newRow[$key]=$cell;
            }
        }
        return $newRow;
    }
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        //
    }
}