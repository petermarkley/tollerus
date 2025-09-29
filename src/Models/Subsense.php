<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use PeterMarkley\Tollerus\Traits\HasTablePrefix;

class Subsense extends Model
{
    use HasTablePrefix;
    protected $table = 'subsenses';
    public $timestamps = false;

    /**
     * Model relations
     */
    public function sense(): BelongsTo
    {
        return $this->belongsTo(Sense::class);
    }
}
