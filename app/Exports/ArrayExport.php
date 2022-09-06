<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ArrayExport extends StringValueBinder implements FromArray, ShouldAutoSize
{
    protected $array;

    public function __construct($array)
    {
        $this->array = $array;
    }

    public function array(): array
    {
        return [
            $this->array
        ];
    }
}
