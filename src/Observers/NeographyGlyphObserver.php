<?php

namespace PeterMarkley\Tollerus\Observers;

use Illuminate\Support\Facades\DB;

use PeterMarkley\Tollerus\Domain\Neography\Actions\BuildGlyphCanonicalRanks;
use PeterMarkley\Tollerus\Domain\Neography\Actions\BuildNativeSpellingSortKeysForNeography;
use PeterMarkley\Tollerus\Domain\Neography\Jobs\BuildNativeSpellingSortKeysForNeographyJob;
use PeterMarkley\Tollerus\Models\Neography;
use PeterMarkley\Tollerus\Models\NeographyGlyph;

class NeographyGlyphObserver
{
    private static array $scheduled = [];

    public function created(NeographyGlyph $glyph): void
    {
        $this->buildRank($glyph);
    }

    public function updated(NeographyGlyph $glyph): void
    {
        if ($glyph->wasChanged(['position', 'group_id'])) {
            $this->buildRank($glyph);
        }
    }

    private function buildRank(NeographyGlyph $glyph): void
    {
        $glyph->loadMissing(['neography']);
        $neography = $glyph->neography;
        if (isset(self::$scheduled[$neography->id])) {
            // Only call action once per request
            return;
        }
        self::$scheduled[$neography->id] = true;
        $connection = config('tollerus.connection', 'tollerus');
        DB::connection($connection)->afterCommit(function () use ($neography) {
            /**
             * Okay, the request is wrapping up. This is our moment.
             */

            // Build glyph ranks
            app(BuildGlyphCanonicalRanks::class)($neography->refresh());

            // Build spelling sort keys
            if (config('queue.default') === 'sync' || !config('tollerus.enable_queue', false)) {
                /**
                 * No job queue, just run the action ...
                 */
                app(BuildNativeSpellingSortKeysForNeography::class)($neography);
            } else {
                /**
                 * The app claims there's a job queue. In that case,
                 * let's not lock up the web page for this, it could
                 * take a minute.
                 */
                // Check and maybe set the 'job queue' flag ...
                $updated = Neography::whereKey($neography->id)
                    ->where('sort_keys_job_queued', false)
                    ->update(['sort_keys_job_queued' => true]);
                if ($updated === 1) {
                    // We successfully claimed the job, so we queue it.
                    dispatch(new BuildNativeSpellingSortKeysForNeographyJob($neography->id))
                        ->delay(now()->addSeconds(30));
                }
            }
        });
    }
}
