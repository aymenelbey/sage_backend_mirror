<?php

namespace App\Exports;

use App\Models\Site;
use App\Models\SocieteExploitant;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Exports\Sheets\SitesPerCategorySheet;

class SitesExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMultipleSheets
{
    use Exportable;

    protected $default_categories = ["UVE", "TRI", "TMB", "ISDND"];
    protected $categories = array();

    protected $data;

    /**
     * Create a new Export instance.
     *
     * @return void
     */
    public function __construct($categories = null)
    {
        if ($categories && is_array($categories))
            foreach ($categories as $category) {
                if (in_array($category, $this->default_categories) && !in_array($category, $this->categories)) array_push($this->categories, $category);
            }
        else
            $this->categories = $this->default_categories;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->data;
    }

    public function headings(): array {
        if ($this->data) return array_keys($this->data->first());
        return [];
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->categories as $category) {
            $sheets[] = new SitesPerCategorySheet($category);
        }

        return $sheets;
    }
}
