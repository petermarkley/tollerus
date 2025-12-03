<?php

namespace PeterMarkley\Tollerus\Models;

use InvalidArgumentException;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

use PeterMarkley\Tollerus\Traits\HasTablePrefix;
use PeterMarkley\Tollerus\Traits\HasGlobalId;
use PeterMarkley\Tollerus\Enums\GlobalIdKind;

final class GlobalId extends Model
{
    use HasTablePrefix;
    use HasGlobalId;
    protected $table = 'global_ids';
    protected $primaryKey = 'global_id_raw';
    protected $keyType = 'int';
    public $timestamps = false;
    protected $casts = ['kind' => GlobalIdKind::class];

    /**
     * Check 'kind' column and dereference
     */
    public function resolve(): ?Model
    {
        $class = $this->kind->model();
        return $class::query()->firstWhere('global_id_raw', $this->global_id_raw);
    }

    /**
     * Convenience: resolve by encoded ID string in one call.
     */
    public static function resolveId(string $globalId): ?Model
    {
        try {
            $rawId = self::decodeGlobalId($globalId);
        } catch (InvalidArgumentException) {
            return null;
        }
        $object = self::query()->find($rawId);
        return $object?->resolve();
    }

    /**
     * Batch resolve, more efficient than repeated single calls to `resolveId()`.
     *
     * Returns a nested collection shaped like:
     *   Collection{
     *     'entry'  => Collection{ global_id(string) => Model },
     *     'lexeme' => Collection{ global_id(string) => Model },
     *     ...
     *   }
     *
     * @param  array<string> $globalIds
     * @return Collection<GlobalIdKind, Collection<string, Model>>
     */
    public static function resolveMany(array $globalIds): Collection
    {
        // decode IDs
        $rawIds = collect($globalIds)
            ->map(function ($item) {
                try {
                    $rawId = self::decodeGlobalId($item);
                } catch (InvalidArgumentException) {
                    return null;
                }
                return $rawId;
            })
            ->filter(fn ($rawId) => $rawId !== null)
            ->unique()
            ->values();

        // no-op if empty
        if ($rawIds->isEmpty()) {
            return collect();
        }

        // fetch list of kinds
        $rows = self::query()->whereKey($rawIds)->get(['global_id_raw','kind']);
        $byKind = $rows->groupBy('kind');

        // run only one query for each kind
        $out = $byKind->map(function ($subset, $kind) {
            // fetch the appropriate model class
            $class = GlobalIdKind::from($kind)->model();

            // flat array of IDs that belong to this kind
            $subsetIds = $subset->pluck('global_id_raw');

            // run the query, save results to output collection
            return $class::query()
                ->whereIn('global_id_raw', $subsetIds)
                ->get()->keyBy('global_id');
        });

        return $out;
    }
}
