<?php

namespace PeterMarkley\Tollerus\Console\Commands;

use Illuminate\Console\Command;

class TollerusInstall extends Command
{
    protected $signature = 'tollerus:install {--force : Overwrite existing files}';
    protected $description = 'Install Tollerus (publish assets + generate background image)';

    public function handle(): int
    {
        // 1. Run vendor:publish
        $this->call('vendor:publish', [
            '--provider' => "PeterMarkley\\Tollerus\\Providers\\TollerusServiceProvider",
            '--tag'      => 'tollerus-config',
            '--force'    => $this->option('force'),
        ]);
        $this->call('vendor:publish', [
            '--provider' => "PeterMarkley\\Tollerus\\Providers\\TollerusServiceProvider",
            '--tag'      => 'tollerus-assets',
            '--force'    => $this->option('force'),
        ]);

        // 2. Generate custom assets
        $this->call('tollerus:assets:generate', [
            '--force' => $this->option('force'),
        ]);

        $this->info('Tollerus installation complete!');
        return self::SUCCESS;
    }
}
