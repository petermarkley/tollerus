<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

use PeterMarkley\Tollerus\Traits\HasTablePrefix;
use PeterMarkley\Tollerus\Traits\HasGlobalId;
use PeterMarkley\Tollerus\Enums\GlobalIdKind;
use PeterMarkley\Tollerus\Models\NeographyGlyph;
use PeterMarkley\Tollerus\Models\Entry;
use PeterMarkley\Tollerus\Models\Lexeme;
use PeterMarkley\Tollerus\Models\Form;

final class GlobalId extends Model
{
    use HasTablePrefix;
    use HasGlobalId;
    protected $table = 'global_ids';
    protected $casts = ['kind' => GlobalIdKind::class];
    public $timestamps = false;

    /**
     * Check 'kind' column and dereference
     */
    public function resolve(): ?Model
    {
        return match ($this->kind) {
            GlobalIdKind::Glyph  => NeographyGlyph::query()->find($this->id),
            GlobalIdKind::Entry  => Entry::query()->find($this->id),
            GlobalIdKind::Lexeme => Lexeme::query()->find($this->id),
            GlobalIdKind::Form   => Form::query()->find($this->id),
        };
    }

    /**
     * Convenience: resolve by encoded ID string in one call.
     */
    public static function resolveId(string $globalId): ?Model
    {
        $id = self::decodeGlobalId($globalId);
        $Object = self::query()->find($id);
        return $Object?->resolve();
    }

    /**
     * Batch resolve, more efficient than repeated single calls to `resolveId()`.
     */
    /*public static function resolveMany(array $globalIds): array
    {
        $ids = collect($globalIds)->map(fn($item) => self::decodeGlobalId($item));
        $rows = static::query()->whereIn('id', $ids)->get(['id','kind']);
        $byKind = $rows->groupBy(fn($item) => $item->kind->value);

        $out = $byKind->map(function ($subset, $kind) {
            // FIXME
        });

        //$out = [];
        //foreach ($byKind as $kind => $subset) {
        //    $class = config("tollerus.kind_model_map.$kind");
        //    if (! $class) continue;
        //    $models = $class::query()->whereIn('id', $subset->pluck('id'))->get()->keyBy('id');
        //    foreach ($subset as $r) { $out[$r->id] = $models->get($r->id); }
        //}
        return $out; // [id => Model|null]
    }*/
}
