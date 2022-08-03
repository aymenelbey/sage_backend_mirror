<?php

namespace App\Exports;

use App\Models\Site;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SitesExport implements FromCollection, WithHeadings
{
    protected $data;
    /**
     * Create a new Export instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Site::all();
    }

    public function headings(): array{
        if ($this->data) return array_keys($this->data->first()->toArray());
        return [];
    }
}
