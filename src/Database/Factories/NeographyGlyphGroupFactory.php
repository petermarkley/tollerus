<?php

namespace PeterMarkley\Tollerus\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use PeterMarkley\Tollerus\Enums\NeographyGlyphType;
use PeterMarkley\Tollerus\Models\NeographyGlyphGroup;

class NeographyGlyphGroupFactory extends Factory
{
    protected $model = NeographyGlyphGroup::class;

    public function definition(): array
    {
        return [
            'type' => NeographyGlyphType::Symbol,
        ];
    }
}