<?php

namespace PeterMarkley\Tollerus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PeterMarkley\Tollerus\Traits\HasTablePrefix;

class Language extends Model
{
    use HasTablePrefix;
    protected $table = 'languages';
    public $timestamps = false;

    /**
     * Model relations
     */
    public function neographies(): BelongsToMany
    {
        return $this
            ->belongsToMany(Neography::class, 'language_neography')
            ->using(Pivots\LanguageNeography::class);
    }
    public function primaryNeography(): BelongsTo
    {
        return $this->belongsTo(Neography::class, 'primary_neography');
    }
    public function wordClassGroups(): HasMany
    {
        return $this->hasMany(WordClassGroup::class);
    }
    public function wordClasses(): HasMany
    {
        return $this->hasMany(WordClass::class);
    }
    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class);
    }
    public function lexemes(): HasMany
    {
        return $this->hasMany(Lexeme::class);
    }
    public function forms(): HasMany
    {
        return $this->hasMany(Form::class);
    }
}
