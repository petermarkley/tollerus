<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use PeterMarkley\Tollerus\Enums\MorphRuleTargetType;
use PeterMarkley\Tollerus\Enums\PatternType;
use PeterMarkley\Tollerus\Traits\HasTablePrefix;

class MorphRule extends Model
{
    use HasTablePrefix;
    protected $table = 'morph_rules';
    public $timestamps = false;
    protected $casts = [
        'target_type' => MorphRuleTargetType::class,
        'pattern_type' => PatternType::class,
    ];
    protected $guarded = [];

    /**
     * Model relations
     */
    public function inflectionTableRow(): BelongsTo
    {
        return $this->belongsTo(InflectionTableRow::class, 'inflect_table_row_id');
    }
    public function neography(): BelongsTo
    {
        return $this->belongsTo(Neography::class);
    }

    /**
     * Scopes
     */
    #[Scope]
    protected function onBase(Builder $query): void
    {
        $query->where('target_type', MorphRuleTargetType::BaseInput);
    }
    #[Scope]
    protected function onParticle(Builder $query): void
    {
        $query->where('target_type', MorphRuleTargetType::ParticleInput);
    }
    #[Scope]
    protected function onTransliterated(Builder $query): void
    {
        $query->where('pattern_type', PatternType::Transliterated);
    }
    #[Scope]
    protected function onPhonemic(Builder $query): void
    {
        $query->where('pattern_type', PatternType::Phonemic);
    }
    #[Scope]
    protected function onNative(Builder $query, int $neographyId): void
    {
        $query->where('pattern_type', PatternType::Native)
            ->where('neography_id', $neographyId);
    }
}
