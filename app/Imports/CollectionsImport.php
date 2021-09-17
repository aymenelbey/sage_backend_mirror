<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMapping;

class CollectionsImport implements ToCollection,WithHeadingRow,WithMapping
{
    public function map($row): array
    {
        $newRow=[];
        foreach($row as $key=>$cell){
            if($cell){
                $newRow[$key]=trim($cell);
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