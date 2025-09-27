<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PeterMarkley\Tollerus\Traits\HasTablePrefix;

class Neography extends Model
{
    use HasTablePrefix;
    protected $casts = [
        'direction_primary' => WritingDirection::class,
        'direction_secondary' => WritingDirection::class,
    ];

    /**
     * The languages that belong to this neography.
     */
    public function languages(): BelongsToMany
    {
        return $this
            ->belongsToMany(Language::class, 'language_neography')
            ->using(\PeterMarkley\Tollerus\Models\Pivots\LanguageNeography::class);
    }

    /**
     * Get the sections for this neography.
     */
    public function neographySections(): HasMany
    {
        return $this->hasMany(NeographySection::class);
    }
}
