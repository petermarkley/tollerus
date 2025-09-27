<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PeterMarkley\Tollerus\Traits\HasTablePrefix;

class Language extends Model
{
    use HasTablePrefix;

    /**
     * The neographies that belong to this language.
     */
    public function neographies(): BelongsToMany
    {
        return $this
            ->belongsToMany(Neography::class, 'language_neography')
            ->using(\PeterMarkley\Tollerus\Models\Pivots\LanguageNeography::class);
    }

    /**
     * Get the word class groups for this language.
     */
    public function wordClassGroups(): HasMany
    {
        return $this->hasMany(WordClassGroup::class);
    }
}
