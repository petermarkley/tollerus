<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use PeterMarkley\Tollerus\Traits\HasTablePrefix;
use PeterMarkley\Tollerus\Database\Factories\NativeSpellingFactory;

class NativeSpelling extends Model
{
    use HasTablePrefix;
    use HasFactory;
    protected $table = 'native_spellings';
    public $timestamps = false;
    protected $guarded = [];

    /**
     * Model relations
     */
    public function neography(): BelongsTo
    {
        return $this->belongsTo(Neography::class);
    }
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    protected static function booted()
    {
        // Validate extended model relations
        static::saving(function (self $model) {
            // Run only when relevant keys changed (or on create)
            if (! $model->isDirty(['form_id', 'neography_id'])) {
                return;
            }
            // If any FK is missing, let DB FKs/uniques handle it.
            if (is_null($model->form_id) || is_null($model->neography_id)) {
                return;
            }
            // Get the language_id via a minimal scalar lookup
            $languageId = Form::query()
                ->whereKey($model->form_id)
                ->value('language_id');
            $formBelongsToLanguage = Pivots\LanguageNeography::query()
                ->where('neography_id', $model->neography_id)
                ->where('language_id', $languageId)
                ->exists();

            if (!$formBelongsToLanguage) {
                throw new \LogicException('NativeSpelling\'s Form must belong to a Language that matches the NativeSpelling\'s Neography');
            }
        });
    }

    /**
     * Factory override
     */
    protected static function newFactory()
    {
        return NativeSpellingFactory::new();
    }
}
