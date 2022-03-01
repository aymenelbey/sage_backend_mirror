<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Imports\CollectionsImport;
use App\Exports\CollectionsExport;
use App\Notifications\DataImportsNotif;
use Excel;

class ImportFromExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:fromexcel {type} {filepath}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This is used to import from excel';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        print_r([
            $this->arguments(),
            Excel::toArray(new CollectionsImport,$this->argument('filepath'))[0]
        ]);
        return 0;
    }
}
