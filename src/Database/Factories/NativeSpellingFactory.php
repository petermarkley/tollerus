<?php

namespace PeterMarkley\Tollerus\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use PeterMarkley\Tollerus\Models\NativeSpelling;

class NativeSpellingFactory extends Factory
{
    protected $model = NativeSpelling::class;

    public function definition(): array
    {
        return [];
    }
}