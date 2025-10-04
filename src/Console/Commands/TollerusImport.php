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
        {--infl= : Path to your inflections XML file}
        {main?* : Paths to your main dictionary XML files}
        {--root= : Path to folder that your XML files consider to be the root folder (for finding font files)}';

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
        $inflectionsFilePath = $this->option('infl');
        $mainFilePaths = $this->argument('main');
        $rootPath = $this->option('root');
        (new FileImportSeeder(
            $inflectionsFilePath,
            $mainFilePaths,
            $rootPath
        ))->run();
    }
}
