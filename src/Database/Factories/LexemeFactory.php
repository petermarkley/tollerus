<?php

namespace PeterMarkley\Tollerus\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use PeterMarkley\Tollerus\Models\Lexeme;
use PeterMarkley\Tollerus\Models\WordClassGroup;

class LexemeFactory extends Factory
{
    protected $model = Lexeme::class;

    public function withForms(
        Language $language,
        WordClassGroup $wordClassGroup
    ): static
    {
        return $this->afterCreating(function (Lexeme $lexeme) use ($language) {
        });
    }

    public function definition(): array
    {
        return [];
    }
}