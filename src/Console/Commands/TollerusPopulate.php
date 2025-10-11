<?php

namespace PeterMarkley\Tollerus\Console\Commands;

use Illuminate\Console\Command;

use PeterMarkley\Tollerus\Models\DisplayTable;
use PeterMarkley\Tollerus\Models\DisplayTableRow;
use PeterMarkley\Tollerus\Models\Feature;
use PeterMarkley\Tollerus\Models\FeatureValue;
use PeterMarkley\Tollerus\Models\Language;
use PeterMarkley\Tollerus\Models\WordClass;
use PeterMarkley\Tollerus\Models\WordClassGroup;
use PeterMarkley\Tollerus\Models\Pivots\DisplayTableFilter;
use PeterMarkley\Tollerus\Models\Pivots\DisplayTableRowFilter;

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
    public function handle()
    {
        $language = Language::factory()
            ->withNeography()
            ->withEnglishGrammar()
            ->create();
    }
}
