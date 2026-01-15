<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use PeterMarkley\Tollerus\Traits\HasTablePrefix;
use PeterMarkley\Tollerus\Database\Factories\SubsenseFactory;

class Subsense extends Model
{
    use HasTablePrefix;
    use HasFactory;
    protected $table = 'subsenses';
    public $timestamps = false;
    protected $guarded = [];

    /**
     * Model relations
     */
    public function sense(): BelongsTo
    {
        return $this->belongsTo(Sense::class);
    }

    /**
     * Factory override
     */
    protected static function newFactory()
    {
        return SubsenseFactory::new();
    }
}
