<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use PeterMarkley\Tollerus\Enums\NeographyGlyphType;
use PeterMarkley\Tollerus\Traits\HasTablePrefix;
use PeterMarkley\Tollerus\Database\Factories\NeographyGlyphGroupFactory;

class NeographyGlyphGroup extends Model
{
    use HasTablePrefix;
    use HasFactory;
    protected $table = 'neography_glyph_groups';
    public $timestamps = false;
    protected $casts = ['type' => NeographyGlyphType::class];

    /**
     * Model relations
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(NeographySection::class, 'section_id');
    }
    public function glyphs(): HasMany
    {
        return $this->hasMany(NeographyGlyph::class, 'group_id');
    }

    /**
     * Factory override
     */
    protected static function newFactory()
    {
        return NeographyGlyphGroupFactory::new();
    }
}
