<?php

namespace PeterMarkley\Tollerus\Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * This seeder imports data from the legacy Tollerus XML file format.
 *
 * To specify a file, run using:
 *
 *   php artisan tollerus:import <FILE_1> <FILE_2>
 *
 * where FILE_1 is the main language XML, and FILE_2 is the
 * inflections XML. If no file is specified, it defaults to
 * "My Conlang" demo data.
 */
class FileImportSeeder extends Seeder
{
    protected string $mainFilePath;
    protected string $inflectionsFilePath;

    /**
     * Accept file paths when creating the seeder manually.
     */
    public function __construct(
        string $mainFilePath = null,
        string $inflectionsFilePath = null
    )
    {
        if (!$mainFilePath) {
            $this->mainFilePath = __DIR__.'/data/myconlang.xml';
        } else {
            $this->mainFilePath = $mainFilePath;
        }
        if (!$inflectionsFilePath) {
            $this->inflectionsFilePath = __DIR__.'/data/myconlang-inflections.xml';
        } else {
            $this->inflectionsFilePath = $inflectionsFilePath;
        }
    }

    public function run(): void
    {
        $mainFile = simplexml_load_file($this->mainFilePath);
        $inflectionsFile = simplexml_load_file($this->inflectionsFilePath);
        
        var_dump($mainFile['title_long']);
    }
}
