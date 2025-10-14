<?php

namespace PeterMarkley\Tollerus\Console\Commands;

use Illuminate\Console\Command;

use PeterMarkley\Tollerus\Domain\Language\Actions\BuildEnglishGrammar;
use PeterMarkley\Tollerus\Models\Entry;
use PeterMarkley\Tollerus\Models\Language;

class TollerusPopulate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tollerus:populate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate random conlang data for dev/testing.';

    /**
     * Execute the console command.
     */
    public function handle(BuildEnglishGrammar $buildEnglishGrammar)
    {
        $language = Language::factory()->withNeography()->create();
        $buildEnglishGrammar($language);
        Entry::factory()
            ->for($language)
            ->withLexemes($language)
            ->count(150)
            ->create();
    }
}
