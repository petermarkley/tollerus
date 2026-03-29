<?php

namespace PeterMarkley\Tollerus\Console\Commands;

use Illuminate\Console\Command;

use PeterMarkley\Tollerus\Domain\Language\Actions\ExportGrammarPreset;
use PeterMarkley\Tollerus\Models\Language;

class TollerusExportGrammarPreset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '
        tollerus:export-grammar-preset
        {lang : The machine name of the language to export}
        {--name= : Human-readable preset name}
        {--description= : Human-readable preset description}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export a language grammar setup as a Tollerus grammar preset';

    /**
     * Execute the console command.
     */
    public function handle(ExportGrammarPreset $exportGrammarPreset): int
    {
        /**
         * Gather inputs
         */
        $locale = app()->getLocale();
        $languageMachineName = $this->argument('lang');
        $language = Language::where('machine_name', $languageMachineName)->first();
        if (!$language) {
            $this->error("No language found with machine_name [{$languageMachineName}].");
            return self::FAILURE;
        }
        $preset = $languageMachineName;
        $this->line("Preset identifier: {$preset}");
        $presetName = $this->option('name');
        $presetDesc = $this->option('description');
        if ($presetName === null) {
            $presetName = $this->ask(
                'Preset name?'
            );
        }
        if ($presetDesc === null) {
            $presetDesc = $this->ask(
                'Preset description?'
            );
        }

        /**
         * Execute domain action
         */
        $exportGrammarPreset(
            language: $language,
            preset: $preset,
            presetName: $presetName,
            presetDesc: $presetDesc,
        );

        /**
         * Print contributing instructions
         */
        $this->newLine();
        $this->info("Grammar preset '{$preset}' exported successfully.");
        $this->newLine();
        $this->line("Files created:");
        $this->line("  storage/app/tollerus/exports/grammar-presets/");
        $this->line("  ├── data/{$preset}.json");
        $this->line("  └── lang/{$locale}/{$preset}.php");
        $this->newLine();
        $this->line("To contribute this preset to Tollerus:");
        $this->line("  1. Fork the Tollerus repository");
        $this->line("  2. Copy data/{$preset}.json into resources/data/grammar_presets/");
        $this->line("  3. Copy lang/{$locale}/{$preset}.php into lang/{$locale}/grammar_presets/");
        $this->line("  4. Commit the files in your fork");
        $this->line("  5. Open a pull request");
        $this->newLine();
        $this->line("Thank you!");
        $this->newLine();
        return self::SUCCESS;
    }
}