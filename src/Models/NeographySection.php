<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PeterMarkley\Tollerus\Enums\NeographySectionType;
use PeterMarkley\Tollerus\Traits\HasTablePrefix;

class NeographySection extends Model
{
    use HasTablePrefix;
    protected $table = 'neography_sections';
    public $timestamps = false;
    protected $casts = ['type' => NeographySectionType::class];

    /**
     * Model relations
     */
    public function neography(): BelongsTo
    {
        return $this->belongsTo(Neography::class);
    }
    public function glyphGroups(): HasMany
    {
        return $this->hasMany(NeographyGlyphGroup::class, 'section_id');
    }
}
