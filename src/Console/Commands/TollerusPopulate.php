<?php

namespace PeterMarkley\Tollerus\Console\Commands;

use Illuminate\Console\Command;

use PeterMarkley\Tollerus\Database\Seeders\DemoConlangSeeder;

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
    protected $description = 'Procedurally generate a full demo conlang: phonology, neography, grammar, lexicon, and entries.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        (new DemoConlangSeeder)->run();
    }
}
