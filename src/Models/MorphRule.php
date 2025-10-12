<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use PeterMarkley\Tollerus\Enums\MorphRuleTargetType;
use PeterMarkley\Tollerus\Traits\HasTablePrefix;

class MorphRule extends Model
{
    use HasTablePrefix;
    protected $table = 'morph_rules';
    public $timestamps = false;
    protected $casts = ['target_type' => MorphRuleTargetType::class];

    /**
     * Model relations
     */
    public function inflectionTableRow(): BelongsTo
    {
        return $this->belongsTo(InflectionTableRow::class);
    }
}
