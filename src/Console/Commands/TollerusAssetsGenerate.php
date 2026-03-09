<?php

namespace PeterMarkley\Tollerus\Console\Commands;

use Illuminate\Console\Command;

use PeterMarkley\Tollerus\Support\AssetBuilders\BackgroundImg;

class TollerusAssetsGenerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tollerus:assets:generate {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the Tollerus dectorative background.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('force')) {
            (new BackgroundImg(
                sourcePath: __DIR__.'/../../../resources/bg_svg_src/glyph_palette.svg',
                outputPath: public_path('vendor/tollerus/bg.svg'),
                seed: null
            ))->generateForce();
        } else {
            (new BackgroundImg(
                sourcePath: __DIR__.'/../../../resources/bg_svg_src/glyph_palette.svg',
                outputPath: public_path('vendor/tollerus/bg.svg'),
                seed: null
            ))->generateIfMissing();
        }
    }
}
