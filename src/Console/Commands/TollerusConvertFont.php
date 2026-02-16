<?php

namespace PeterMarkley\Tollerus\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

use PeterMarkley\Tollerus\Domain\Neography\Services\FontAssetService;
use PeterMarkley\Tollerus\Enums\FontFormat;
use PeterMarkley\Tollerus\Models\Neography;

class TollerusConvertFont extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tollerus:convert-font {neography} {src_format} {dest_format}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Use FontForge CLI to convert the font of the given neography (specified by its machine_name).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get context
        $fontName = $this->argument('neography');
        $neography = Neography::where('machine_name', $fontName)->first();
        $srcFormat = FontFormat::from($this->argument('src_format'));
        $destFormat = FontFormat::from($this->argument('dest_format'));
        $srcFilePath = $neography->{$srcFormat->pathColumn()};
        if (!$srcFilePath || !is_file($srcFilePath)) {
            throw new \RuntimeException("Source file does not exist.");
        }
        if (!empty($neography->{$destFormat->blobColumn()})) {
            throw new \RuntimeException("Destination format already exists.");
        }
        $destFilePath = '/tmp/tollerus-' . $fontName . '.' . $destFormat->extension();

        // Convert
        $result = Process::run("fontforge -lang=ff -c 'Open($1); Generate($2)' \"{$srcFilePath}\" \"{$destFilePath}\"");
        if (!$result->successful()) {
            throw new \RuntimeException($result->errorOutput());
        }

        // Save and publish
        $neography->{$destFormat->blobColumn()} = file_get_contents($destFilePath);
        $neography->save();
        app(FontAssetService::class)->publish($destFormat, $neography);

        // Remove temporary file
        unlink($destFilePath);
    }
}
