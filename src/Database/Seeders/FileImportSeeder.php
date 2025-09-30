<?php

namespace PeterMarkley\Tollerus\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * This seeder imports data from the legacy Tollerus XML file format.
 *
 * To specify a file, run using:
 *
 *   php artisan tollerus:import [--infl=<FILE_1>] <FILE_2> ...
 *
 * where FILE_1 is the inflections XML, and subsequent files are the
 * main dictionary files. If no files are specified, it defaults to
 * "My Conlang" demo data.
 */
class FileImportSeeder extends Seeder
{
    protected string $inflectionsFilePath;
    protected array $mainFilePaths;

    /**
     * Accept file paths when creating the seeder manually.
     */
    public function __construct(
        string $inflectionsFilePath = null,
        array $mainFilePaths = []
    )
    {
        /**
         * Only if no arguments are provided will it revert to the demo data
         */
        if (!$inflectionsFilePath && count($mainFilePaths) < 1) {
            $this->inflectionsFilePath = __DIR__.'/data/myconlang-inflections.xml';
            $this->mainFilePaths = [__DIR__.'/data/myconlang.xml'];
        } else {
            $this->inflectionsFilePath = $inflectionsFilePath;
            $this->mainFilePaths = $mainFilePaths;
        }
    }

    public function run(): void
    {
        // Check for & read inflections file
        if ($this->inflectionsFilePath) {
            $inflectionsFile = simplexml_load_file($this->inflectionsFilePath);
        } else {
            $inflectionsFile = null;
        }
        // Check for & read main dictionary files
        $mainFiles = collect($this->mainFilePaths)
            ->map(
                fn($item) => simplexml_load_file($item)
            );

        var_dump($mainFiles->first()['title_long']);
    }
}
