<?php

namespace PeterMarkley\Tollerus\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use PeterMarkley\Tollerus\Models\NeographyGlyph;

class NeographyGlyphFactory extends Factory
{
    protected $model = NeographyGlyph::class;

    public function definition(): array
    {
        return [
            'render_base' => false,
        ];
    }
}