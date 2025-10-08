<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use PeterMarkley\Tollerus\Enums\NeographyGlyphType;
use PeterMarkley\Tollerus\Traits\HasTablePrefix;
use PeterMarkley\Tollerus\Traits\HasGlobalId;
use PeterMarkley\Tollerus\Database\Factories\NeographyGlyphFactory;

class NeographyGlyph extends Model
{
    use HasTablePrefix;
    use HasGlobalId;
    use HasFactory;
    protected $table = 'neography_glyphs';
    public $timestamps = false;

    /**
     * Model relations
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(NeographyGlyphGroup::class, 'group_id');
    }
    public function neography(): BelongsTo
    {
        return $this->belongsTo(Neography::class);
    }

    protected static function booted()
    {
        // Validate extended model relations
        static::saving(function (self $model) {
            // Run only when relevant keys changed (or on create)
            if (! $model->isDirty(['group_id', 'neography_id'])) {
                return;
            }
            // If any FK is missing, let DB FKs/uniques handle it.
            if (is_null($model->group_id) || is_null($model->neography_id)) {
                return;
            }
            // Get the neography_id via a minimal scalar lookup
            $sectionId = NeographyGlyphGroup::query()
                ->whereKey($model->group_id)
                ->value('section_id');
            $groupBelongsToNeography = NeographySection::query()
                ->whereKey($sectionId)
                ->where('neography_id', $model->neography_id)
                ->exists();

            if (!$groupBelongsToNeography) {
                throw new \LogicException('NeographyGlyph.neography_id must match its group\'s NeographySection.neography_id');
            }
        });
    }

    /**
     * Factory override
     */
    protected static function newFactory()
    {
        return NeographyGlyphFactory::new();
    }
}
