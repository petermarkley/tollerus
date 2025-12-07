<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

use PeterMarkley\Tollerus\Enums\WritingDirection;
use PeterMarkley\Tollerus\Traits\HasTablePrefix;
use PeterMarkley\Tollerus\Database\Factories\NeographyFactory;

class Neography extends Model
{
    use HasTablePrefix;
    use HasFactory;
    protected $table = 'neographies';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = [
        'direction_primary' => WritingDirection::class,
        'direction_secondary' => WritingDirection::class,
    ];

    /**
     * Model relations
     */
    public function languages(): BelongsToMany
    {
        return $this
            ->belongsToMany(Language::class, 'language_neography')
            ->using(Pivots\LanguageNeography::class);
    }
    // languages whose primary neography is this one
    public function languagesWherePrimary(): HasMany
    {
        return $this->hasMany(Language::class, 'primary_neography');
    }
    public function sections(): HasMany
    {
        return $this->hasMany(NeographySection::class);
    }
    public function glyphs(): HasMany
    {
        return $this->hasMany(NeographyGlyph::class);
    }
    public function nativeSpellings(): HasMany
    {
        return $this->hasMany(NativeSpellings::class);
    }

    /**
     * Factory override
     */
    protected static function newFactory()
    {
        return NeographyFactory::new();
    }
}
