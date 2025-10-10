<?php

namespace PeterMarkley\Tollerus\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use PeterMarkley\Tollerus\Models\WordClassGroup;

class WordClassGroupFactory extends Factory
{
    protected $model = WordClassGroup::class;

    public function definition(): array
    {
        return [
            'inflected' => false,
        ];
    }
}