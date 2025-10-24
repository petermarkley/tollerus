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

    /**
     * Find the SVG data of this glyph. Return it as a
     * standalone SVG image, or null on failure
     */
    public function getSvg(string $classes = null): string|null
    {
        // Initialize
        $neography = $this->neography;
        if ($neography === null || $neography->font_svg === null) {
            return null;
        }
        $svg = simplexml_load_string($neography->font_svg);
        if ($svg === false) {
            return null;
        }
        if (!isset($svg->defs->font)) {
            return null;
        }
        $font = $svg->defs->font;
        $path = "";
        // Find this glyph inside the SVG font
        foreach ($font->glyph as $glyph) {
            if ($glyph['unicode'] == $this->glyph) {
                $d = $glyph['d'];
                $path = "<path fill=\"currentColor\" d=\"$d\"/>";
                $width = $glyph['horiz-adv-x'];
                break;
            }
        }
        // If not found, give up ...
        if (empty($path)) {
            return null;
        }
        // Time to build output image
        if ($this->render_base) {
            /**
             * Above, we set $width equal to the horizontal advance of the glyph.
             * However, for combining marks this might be close to zero, meaning
             * the glyph shape would be off the canvas and wouldn't be visible
             * if our SVG image is displayed. So for combining marks we use the
             * canvas width of the font file instead of the glyph advance.
             */
            $width = $svg['width'];
        }
        $height = $svg['height'];
        $viewBox = "0 0 $width $height";
        if ($classes === null) {
            $output[] = "<svg width=\"$width\" height=\"$height\" viewBox=\"$viewBox\">";
        } else {
            $output[] = "<svg width=\"$width\" height=\"$height\" viewBox=\"$viewBox\" class=\"$classes\">";
        }
        $output[] = $path;
        $output[] = "</svg>";
        $output[] = "";
        return implode("\n",$output);
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
