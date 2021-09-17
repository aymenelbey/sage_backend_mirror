<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;

class CollectionsExport extends StringValueBinder implements FromArray,WithHeadings,ShouldAutoSize,WithCustomValueBinder
{
    protected $data;
    /**
     * Create a new Export instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data=$data;
    }
    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array{
        if(isset($this->data[0])) return array_keys($this->data[0]);
        return [];
    }
}