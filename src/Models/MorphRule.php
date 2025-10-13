<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use PeterMarkley\Tollerus\Enums\MorphRuleTargetType;
use PeterMarkley\Tollerus\Traits\HasTablePrefix;

class MorphRule extends Model
{
    use HasTablePrefix;
    protected $table = 'morph_rules';
    public $timestamps = false;
    protected $casts = ['target_type' => MorphRuleTargetType::class];
    protected $guarded = [];

    /**
     * Model relations
     */
    public function inflectionTableRow(): BelongsTo
    {
        return $this->belongsTo(InflectionTableRow::class, 'inflect_table_row_id');
    }

    /**
     * Scopes
     */
    #[Scope]
    protected function onBase(Builder $query): void
    {
        $query->where('target_type', MorphRuleTargetType::BaseInput)
            ->orderBy('order');
    }
    #[Scope]
    protected function onCombiningForm(Builder $query): void
    {
        $query->where('target_type', MorphRuleTargetType::CombiningInput)
            ->orderBy('order');
    }
}
