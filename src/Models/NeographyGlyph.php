<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use PeterMarkley\Tollerus\Enums\NeographyGlyphType;
use PeterMarkley\Tollerus\Traits\HasTablePrefix;
use PeterMarkley\Tollerus\Traits\HasGlobalId;

class NeographyGlyph extends Model
{
    use HasTablePrefix;
    use HasGlobalId;
    protected $table = 'neography_glyphs';
    public $timestamps = false;
    protected $casts = ['type' => NeographyGlyphType::class];

    /**
     * Model relations
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(NeographySection::class, 'section_id');
    }
    public function neography(): BelongsTo
    {
        return $this->belongsTo(Neography::class);
    }

    protected static function booted()
    {
        // Validate extended model relations
        static::saving(function (self $model) {
            if ($model->section && ($model->neography_id !== $model->section->neography_id)) {
                throw new \LogicException('NeographyGlyph.neography_id must match its section.neography_id');
            }
        });
    }
}
