<?php

namespace PeterMarkley\Tollerus\Console\Commands;

use Illuminate\Console\Command;

use PeterMarkley\Tollerus\Database\Seeders\FileImportSeeder;

class TollerusImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tollerus:import
        {main_file : Path to your main dictionary XML file}
        {inflections_file : Path to your inflections XML file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data from the legacy Tollerus XML file format.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mainFilePath = $this->argument('main_file');
        $inflectionsFilePath = $this->argument('inflections_file');
        (new FileImportSeeder(
            $mainFilePath,
            $inflectionsFilePath
        ))->run();
    }
}
