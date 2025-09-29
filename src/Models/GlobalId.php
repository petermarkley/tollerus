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
    public $timestamps = false;
    protected $casts = ['kind' => GlobalIdKind::class];

    /**
     * Check 'kind' column and dereference
     */
    public function resolve(): ?Model
    {
        $class = $this->kind->model();
        return $class::query()->find($this->id);
    }

    /**
     * Convenience: resolve by encoded ID string in one call.
     */
    public static function resolveId(string $globalId): ?Model
    {
        try {
            $id = self::decodeGlobalId($globalId);
        } catch (InvalidArgumentException) {
            return null;
        }
        $object = self::query()->find($id);
        return $object?->resolve();
    }

    /**
     * Batch resolve, more efficient than repeated single calls to `resolveId()`.
     *
     * Returns a nested collection shaped like:
     *   Collection{
     *     GlobalIdKind::Entry  => Collection{ global_id(string) => Model },
     *     GlobalIdKind::Lexeme => Collection{ global_id(string) => Model },
     *     ...
     *   }
     *
     * @param  array<string> $globalIds
     * @return Collection<GlobalIdKind, Collection<string, Model>>
     */
    public static function resolveMany(array $globalIds): Collection
    {
        // decode IDs
        $ids = collect($globalIds)
            ->map(function ($item) {
                try {
                    $id = self::decodeGlobalId($item);
                } catch (InvalidArgumentException) {
                    return null;
                }
                return $id;
            })
            ->filter(fn ($id) => $id !== null)
            ->unique()
            ->values();

        // no-op if empty
        if ($ids->isEmpty()) {
            return collect();
        }

        // fetch list of kinds
        $rows = self::query()->whereIn('id', $ids)->get(['id','kind']);
        $byKind = $rows->groupBy('kind');

        // run only one query for each kind
        $out = $byKind->map(function ($subset, $kind) {
            // fetch the appropriate model class
            $class = $kind->model();

            // flat array of IDs that belong to this kind
            $subsetIds = $subset->pluck('id');

            // run the query, save results to output collection
            return $class::query()
                ->whereIn('id', $subsetIds)
                ->get()->keyBy('global_id');
        });

        return $out;
    }
}
