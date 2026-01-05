<?php

namespace PeterMarkley\Tollerus\Domain\Neography\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

use PeterMarkley\Tollerus\Domain\Neography\Actions\BuildNativeSpellingSortKeysForNeography;
use PeterMarkley\Tollerus\Models\Neography;

class BuildNativeSpellingSortKeysForNeographyJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $neographyId,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(BuildNativeSpellingSortKeysForNeography $action): void
    {
        // Reset 'job queued' flag, so other processes can queue more jobs again.
        Neography::whereKey($this->neographyId)->update(['sort_keys_job_queued' => false]);

        // Do the thing.
        $neographyModel = Neography::findOrFail($this->neographyId);
        $action($neographyModel);
    }
}