<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use PeterMarkley\Tollerus\Enums\NeographySectionType;
use PeterMarkley\Tollerus\Traits\HasTablePrefix;
use PeterMarkley\Tollerus\Database\Factories\NeographySectionFactory;

class NeographySection extends Model
{
    use HasTablePrefix;
    use HasFactory;
    protected $table = 'neography_sections';
    public $timestamps = false;
    protected $guarded = [];
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

    /**
     * Factory override
     */
    protected static function newFactory()
    {
        return NeographySectionFactory::new();
    }
}
